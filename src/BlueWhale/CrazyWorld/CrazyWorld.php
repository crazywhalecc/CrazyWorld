<?php
/*
		[CrazyWorld]
		A stable Multi-World plugin for PocketMine-MP
		Twitter: @BlockForWhale 
		QQ: 627577391
		Copyright © BlueWhaleNetwork | 2017, Whale
		Please keep author's name and copyright when modifiying.
		Thanks!
		The plugin is updating. Please check my git http://github.com/BlueWhaleNetwork
*/
namespace BlueWhale\CrazyWorld;

use pocketmine\level\sound\NoteblockSound;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\level\generator\Generator;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Utils;
use pocketmine\utils\TextFormat as TM;

use BlueWhale\CrazyWorld\Generators\Land;
use BlueWhale\CrazyWorld\Generators\EmptyWorld;
use BlueWhale\CrazyWorld\Generators\SnowLand;
use BlueWhale\CrazyWorld\Generators\WoodFlat;

class CrazyWorld extends PluginBase
{
	public static $obj=null;
	public $lang = array();
	public $config;
	public $sound = 0;
	
	public function onLoad()//Loading
	{
		self::$obj=$this;
		/*$t=$this->tokenCheck();
		if($t!=true)
		{
			exit();
			$this->getServer()->shutdown();
			$this->getServer()->forceShutdown();
			return false;
		}*/
		$this->path=$this->getDataFolder();
	}
	public static function getInstance()//API
	{
		return self::$obj;
	}

	public function myMusic(Player $sender)
    {
        $this->sound++;
        if($this->sound > 12)
        {
            $this->sound = 0;
        }

        $sender->level->addSound(new NoteblockSound($sender,0,$this->sound));
        $sender->sendTip("播放音效！".$this->sound);
    }
	private function tokenCheck()
	{
		$ip=self::getServerIP();
		$port=$this->getServer()->getPort();
		if($ip === false)
		{
			$errmsg="服务器未连接到互联网！";
			$this->getServer()->getLogger()->warning(TM::RED."[CrazyWorld] 对不起，CrazyWorld授权失败！");
			$this->getServer()->getLogger()->warning(TM::RED."[CrazyWorld] 错误信息: ".$errmsg);
			return false;
		}
		try
		{
			$this->getServer()->getLogger()->info(TM::AQUA."[CrazyWorld] 正在连接授权服务器 <1>...");
			$info = json_decode(Utils::getURL("bluewhale.bj01.bdysite.com/check/crazyworld.json"), true);
			if(!isset($info["status"]) or $info["status"] !== true)
			{
				$this->getServer()->getLogger()->info(TM::YELLOW."[CrazyWorld] 授权服务器 <1> 连接失败！");
				return $this->checkOld2();
			}
			$list=$info["list"];
			$ins=$ip.":".$port;
			if(!in_array($ins,$list))
			{
				$errmsg="插件未授权此服务器！"."地址: ".$ins;
				$this->getServer()->getLogger()->warning(TM::RED."[CrazyWorld] 对不起，CrazyWorld授权失败！");
				$this->getServer()->getLogger()->warning(TM::RED."[CrazyWorld] 错误信息: ".$errmsg);
				return false;
			}
			else
			{
				$this->getServer()->getLogger()->warning(TM::GREEN."[CrazyWorld] 成功授权，欢迎使用CrazyWorld!");
				return true;
			}
		}
		catch(\Throwable $e)
		{
			$this->getLogger()->logException($e);
			$this->getServer()->getLogger()->info(TM::YELLOW."[CrazyWorld] 授权服务器 <1> 连接失败！");
			return $this->checkOld2();
		}
	}
	private function checkOld2()
	{
		try
		{
			$this->getServer()->getLogger()->info(TM::AQUA."[CrazyWorld] 正在连接授权服务器 <2>...");
			$info = json_decode(Utils::getURL("crazysnow.cc:8081/check/crazyworld.json"), true);
			if(!isset($info["status"]) or $info["status"] !== true)
			{
				$this->getServer()->getLogger()->info(TM::YELLOW."[CrazyWorld] 授权服务器 <2> 连接失败！");
				$this->getServer()->getLogger()->info(TM::RED."[CrazyWorld] 警告，无法连接任何授权服务器！");
				return false;
			}
			$list=$info["list"];
			$ins=$ip.":".$port;
			if(!in_array($ins,$list))
			{
				$errmsg="插件未授权此服务器！地址: ".$ins;
				$this->getServer()->getLogger()->warning(TM::RED."[CrazyWorld] 对不起，CrazyWorld授权失败！");
				$this->getServer()->getLogger()->warning(TM::RED."[CrazyWorld] 错误信息: ".$errmsg);
				return false;
			}
			else
			{
				$this->getServer()->getLogger()->warning(TM::GREEN."[CrazyWorld] 成功授权，欢迎使用CrazyWorld!");
				return true;
			}
		}
		catch(\Throwable $e)
		{
			$this->getServer()->getLogger()->info(TM::YELLOW."[CrazyWorld] 授权服务器 <2> 连接失败！");
			$this->getServer()->getLogger()->info(TM::RED."[CrazyWorld] 警告，无法连接任何授权服务器！");
			return false;
		}
	}
	public function delMapData($dir) 
	{
		$dh = opendir($dir);
		while ($file=readdir($dh))
		{
			if($file!="." && $file!="..") 
			{
				$fullpath = $dir."/".$file;
				if(!is_dir($fullpath))
				{
					@unlink($fullpath);
				}
				else
				{
					$this->delMapData($fullpath);
				}
			}
		}
		closedir($dh);
		if(@rmdir($dir)) 
		{
			return true;
		} 
		else 
		{
			return false;
		}
	}
	private static function getServerIP()
	{
		if(Utils::$online === false){
			return false;
		}elseif(Utils::$ip !== false and $force !== true){
			return Utils::$ip;
		}
		$ip = trim(strip_tags(Utils::getURL("https://api.ipify.org")));
		if($ip){
			Utils::$ip = $ip;
		}else{
			$ip = Utils::getURL("http://www.checkip.org/");
			if(preg_match('#">([0-9a-fA-F\:\.]*)</span>#', $ip, $matches) > 0){
				Utils::$ip = $matches[1];
			}else{
				$ip = Utils::getURL("http://checkmyip.org/");
				if(preg_match('#Your IP address is ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
					Utils::$ip = $matches[1];
				}else{
					$ip = trim(Utils::getURL("http://ifconfig.me/ip"));
					if($ip != ""){
						Utils::$ip = $ip;
					}else{
						return false;
					}
				}
			}
		}

		return Utils::$ip;
	}
	public function onEnable()//Start
	{
		$this->registerGenerator();
		$this->createConfig();
		$this->registerListener();
		$this->lang=$this->loadLanguage($this->config->get("language"));
		$this->loadLevels();
		$this->registerCommands();
	}
	private function registerListener()//Listener Registering
	{
		$this->eventClass=new EventListener($this);
	}
	private function registerGenerator()//Add more generator
	{
		Generator::addGenerator(Land::class, "land");
		Generator::addGenerator(SnowLand::class, "snowland");
		Generator::addGenerator(EmptyWorld::class, "empty");
		Generator::addGenerator(WoodFlat::class, "woodflat");
	}
	public function loadLanguage($lang)//加载语言 & Load language
	{
		switch($lang)
		{
			case "eng"://English
			case "ch"://中文简体
			case "cho"://中文繁體
			/*case "de"://Deutsch
			case "jp"://日本語
			case "fr"://Français
			case "kr"://한국어 */
				return $this->language->get($lang);
			default:
				return $this->language->get("eng");
		}
	}
	private function createConfig()//create config
	{
		@mkdir($this->path);
		$this->config = new Config($this->path."config.json",Config::JSON,array(
			"Config-Version" => 1,//Inactive
			"language" => "ch",//Active
			"admin" => array(),//Active
			"item-touch"=>array(259,325,351,291,292,293,294),//Active
			"protect-world" => array(),//Active
			"protect-msg-type" => 1,//Active
			"banpvp-world" => array(),//Active
			"banpvp-msg-type" => 0,//Active
			"allow-op-pvp" => false,//Active
			"lock-gm-world" => array(),//Aactive
			"allow-op-change-gm" => true,//Inactive
			"white-list-world" => array(),//Active
			
		));
		$this->language=new Config($this->path."msg.yml",Config::YAML,LanguagePack::getLanguage);
		//$this->saveResource("msg.yml");
		$this->command=new Config($this->path."command.yml",Config::YAML,array(
			"MainCommand" => array(
				"command" => "cw",
				"permission" => "true",
				"description" => "§eCrazyWorld general Setting"
			),
			"WorldTpCommand" => array(
				"command" => "w",
				"permission" => "true",
				"description" => "§bCrazyWorld world teleport command"
			),
			"MakeMapCommand" => array(
				"command" => "mw",
				"permission" => "true",
				"description" => "§bCrazyWorld MakeMap command"
			),
			"TpCommand" => array(
				"command" => "go",
				"permission" => "true",
				"description" => "§bCrazyWorld teleport command"
			)
		));
		$this->position=new Config($this->path."position.yml",Config::YAML,array());
	}
	public function loadLevels()//load all world on server
	{
		$level = $this->getServer()->getDefaultLevel();
		$path = $level->getFolderName();
		$p1 = dirname($path);
		$p2 = $p1."/worlds/";
		$dirnowfile = scandir($p2, 1);
		foreach ($dirnowfile as $dirfile)
		{
			if($dirfile != '.' && $dirfile != '..' && $dirfile != $path && is_dir($p2.$dirfile))
			{
				if (!$this->getServer()->isLevelLoaded($dirfile))
				{
					$this->getServer()->getLogger()->info(str_replace("{level}",$dirfile,$this->lang["load-world-msg"]));
					//$this->getServer()->generateLevel($dirfile);
					$this->getServer()->loadLevel($dirfile);
				}
			}
		}
	}
	public function isManager($player)//Check admin
	{
		if($player instanceof Player)
		{
			$player=$player->getName();
		}
		elseif($player instanceof ConsoleCommandSender)
		{
			return true;
		}
		$list=$this->config->get("admin");
		if(in_array($player,$list))
			return true;
		else
			return false;
	}
	private function registerCommands()//register Commands
	{
		foreach($this->command->getAll() as $cmdName=>$function)
		{
			$map = $this->getServer()->getCommandMap();
			$class = "\\BlueWhale\\CrazyWorld\\Commands\\".$cmdName;
			$map->register("CrazyWorld", new $class($this));
		}
	}
	public function sendMsgPacket($p,$msg,$type)//Send the Message Packet
	{
		switch($type)
		{
			case 0:
				$pk = new \pocketmine\network\protocol\TextPacket;
				$pk->message = $this->msgs($msg,$p);
				$pk->type = \pocketmine\network\protocol\TextPacket::TYPE_RAW;
				$pk->buffer = "task";
				$p->dataPacket($pk);
				return;
			case 1:
				$pk = new \pocketmine\network\protocol\TextPacket;
				$pk->message = $this->msgs($msg,$p);
				$pk->type = \pocketmine\network\protocol\TextPacket::TYPE_TIP;
				$pk->buffer = "task" ;
				$p->dataPacket($pk);
				return;
			case 2:
				$pk = new \pocketmine\network\protocol\TextPacket;
				$pk->message = $this->msgs($msg,$p);
				$pk->type = \pocketmine\network\protocol\TextPacket::TYPE_POPUP;
				$pk->buffer = "task" ;
				$p->dataPacket($pk);
				return;
			case 3:
				$pk = new \pocketmine\network\protocol\TextPacket;
				$pk->message = $this->msgs($msg,$p);
				$pk->type = \pocketmine\network\protocol\TextPacket::TYPE_WHISPER;
				$pk->buffer = "task" ;
				$p->dataPacket($pk);
				return;
		}
	}
	public function msgs($msg,$p = null)//Message Tag
	{
		$tps=(string)$this->getServer()->getTicksPerSecondAverage();
		$minitime=microtime(true) - \pocketmine\START_TIME;
		$uptime = (int)($minitime/60);
		$load =(string) $this->getServer()->getTickUsageAverage();
		$load=$load."%";
		$time=date("H").": ".date("i").": ".date("s");
		if($p !== null)
		{
			if($this->getServer()->getPluginManager()->getPlugin('EconomyAPI') !== null)
			{
				$m = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI')->getInstance()->myMoney(strtolower($p->getName()));
				$msg=str_replace("{money}",$m,$msg);
			}
			$beibao = $p->getInventory();
			$item = $beibao->getItemInHand();
			$id = $item->getID();
			$ts = $item->getDamage();
			$lv = $p->getLevel()->getFolderName();
			$food=$p->getFood();
			$x=(int)($p->x);$y=(int)($p->y);$z=(int)($p->z);
			$msg=str_replace("%p",$p->getName(),$msg);
			$msg=str_replace("{name}",$p->getName(),$msg);
			$msg=str_replace("{hp}",$p->getHealth(),$msg);
			$msg=str_replace("{mhp}",$p->getMaxHealth(),$msg);
			$msg=str_replace("{itemid}",$id,$msg);
			$msg=str_replace("{itemdamage}",$ts,$msg);
			$msg=str_replace("{level}",$lv,$msg);
			$msg=str_replace("{food}",$food,$msg);
			$msg=str_replace("{ip}",$p->getAddress(),$msg);
			$msg=str_replace("{port}",$p->getPort(),$msg);
			$msg=str_replace("{x}",$x,$msg);
			$msg=str_replace("{y}",$y,$msg);
			$msg=str_replace("{z}",$z,$msg);
			$isop=$p->isOp() ? "OP" : "Normal";
			$msg=str_replace("{isop}",$isop,$msg);
		}
		$pc=0;
		foreach($this->getServer()->getOnlinePlayers() as $pkk)
		{if($pkk->isOnline()){++$pc;}unset($pkk);}
		
		$msg=str_replace("%n","\n",$msg);
		$msg=str_replace("+"," ",$msg);
		$msg=str_replace("{time}",$time,$msg);
		
		$msg=str_replace("{tps}",$tps,$msg);
		$msg=str_replace("{online}",$pc,$msg);
		
		$msg=str_replace("{load}",$load,$msg);
		$msg=str_replace("{runtime}",$uptime,$msg);
		unset($tps,$time,$m,$beibao,$item,$id,$ts,$pc,$load);
		return $msg;
	}
	public function setNewLevel($level,$type)
	{
		if(!$this->getServer()->isLevelGenerated($level))
		{
			$seed=1;
			$opts=[];
			if(!in_array($type,Generator::getGeneratorList()))
			{
				return 1;
			}
			$gen=Generator::getGenerator($type);
			$this->getServer()->generateLevel($level,$seed,$gen,$opts);
			$this->getServer()->loadLevel($level);
			return true;
		}
		else
		{
			return false;
		}
	}
}
