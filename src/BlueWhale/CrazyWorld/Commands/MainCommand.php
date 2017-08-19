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
namespace BlueWhale\CrazyWorld\Commands;

use BlueWhale\CrazyWorld\CrazyWorld;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\nbt\tag\StringTag;
use BlueWhale\CrazyWorld\PHPMailer;
use pocketmine\scheduler\CallbackTask;

class MainCommand extends Command
{
	private $plugin;
	
	public function __construct(CrazyWorld $plugin)
	{
		$desc=$plugin->command->get("MainCommand");
		parent::__construct($desc["command"], $desc["description"]);
		$permission=($desc["permission"] == "true") ? "cw.command.true" : ($desc["permission"] == "op" ? "cw.command.op" : "cw.command.op");
		$this->setPermission($permission);
		$this->plugin = $plugin;
		$this->cmd=$desc["command"];
		$c=$this->cmd;
		$tpcmd=$this->plugin->command->get("TpCommand");
		$tpcmd=$tpcmd["command"];
		$wcmd=$this->plugin->command->get("WorldTpCommand");
		$wcmd=$wcmd["command"];
		$mwcmd=$this->plugin->command->get("MakeMapCommand");
		$mwcmd=$mwcmd["command"];
		$this->cmdHelp=str_replace(["{cmd}","%tpcmd","%wcmd","%mwcmd"],[$c,$tpcmd,$wcmd,$mwcmd],$this->plugin->lang["main-help"]);
		$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
	}
	public function sendLog($m)
	{
		$this->plugin->getServer()->getLogger()->notice($m);
	}
	public function execute(CommandSender $sender, $label, array $args)//Command Executer
	{
		if(!$this->plugin->isEnabled())
			return false;
		if(!$this->plugin->isManager($sender) && $sender->isOp())
		{
			$sender->sendMessage(str_replace("%n","\n",str_replace("{cmd}",$this->cmd,$this->plugin->lang["not-admin-op-msg"])));
			return true;
		}
		elseif(!$this->plugin->isManager($sender) && !$sender->isOp())
		{
			$sender->sendMessage($this->plugin->lang["not-admin-player-msg"]);
			return true;
		}
		if(isset($args[0]))
		{
			switch($args[0])
			{
				case "send":
					if(isset($args[1]))
					{
						$msg=$args[1];
						$mymailbox="627577391@qq.com";
						$originmail="crazysnowteam@163.com";
						$server="smtp.163.com";
						$mail=new PHPMailer();
						$mail->isSMTP();                                      // Set mailer to use SMTP
						$mail->Host = $server;  // Specify main and backup SMTP servers
						$mail->SMTPAuth = true;                               // Enable SMTP authentication
						$mail->Username = $originmail;                 // SMTP username
						$mail->Password = '312645Mjy';                           // SMTP password
						//$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
						$mail->Port = 25;                                    // TCP port to connect to

						$mail->setFrom($originmail, 'CrazySnow官方团队');
						$mail->addAddress($mymailbox);                         // Set email format to HTML
						$mail->isHTML();
						$mail->Subject = '[请勿回复此邮件]CrazySnow服务器改密';
						$mail->Body    = '这是你的验证码 <b>201568</b>';

						if(!$mail->send()) {
							$this->sendLog( 'Message could not be sent.');
							$this->sendLog( 'Mailer Error: ' . $mail->ErrorInfo);
						} else 
						{
						$this->sendLog('Message has been sent');}
						return true;
					}
                case "m":
                    $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this->plugin,"myMusic"],[$sender]),20);
                    return true;
				case "protect":
				case "保护":
					if(isset($args[1]))
					{
						$list=$this->plugin->config->get("protect-world");
						if(in_array($args[1],$list))
						{
							$inv = array_search($args[1],$list);
							array_splice($list, $inv, 1); 
							$this->plugin->config->set("protect-world",$list);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{level}",$args[1],$this->plugin->lang["del-protect-msg"]));
							return true;
						}
						else
						{
							$list[]=$args[1];
							$this->plugin->config->set("protect-world",$list);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{level}",$args[1],$this->plugin->lang["add-protect-msg"]));
							return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["protect-cmd-usage"])); 
						return true;
					}
				case "admin":
				case "管理员":
					if($sender instanceof Player)
					{
						$sender->sendMessage($this->plugin->lang["not-console-admin"]);
						return true;
					}
					if(isset($args[1]))
					{
						$name=$args[1];
						$managers=$this->plugin->config->get("admin");
						if(in_array($name,$managers))
						{
							$inv = array_search($name, $managers);
							array_splice($managers, $inv, 1); 
							$this->plugin->config->set("admin",$managers);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{name}",$name,$this->plugin->lang["del-admin-msg"]));
							return true;
						}
						else
						{
							$managers[]=$name;
							$this->plugin->config->set("admin",$managers);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{name}",$name,$this->plugin->lang["add-admin-msg"]));
							return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["admin-cmd-usage"]));
						return true;
					}
				case "load":
					if(isset($args[1]))
					{
						$level = $this->plugin->getServer()->getDefaultLevel();
						$path = $level->getFolderName();
						$p1 = dirname($path);
						$p2 = $p1."/worlds/";
						$path = $p2;
						$l = $args[1];
						if ($this->plugin->getServer()->isLevelLoaded($l)) 
						{
							$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["load-failed-loaded-msg"]));
							return true;
						}
						elseif(is_dir($path.$l))
						{
							$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["load-world-msg"]));
							$this->plugin->getServer()->generateLevel($l);
							$ok = $this->plugin->getServer()->loadLevel($l);
							if ($ok === false) 
							{
								$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["load-failed-msg"]));
							}
							else 
							{
								$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["load-success-msg"]));
							}
						}
						else
						{
							$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["load-failed-exists-msg"]));
						}
						return true;
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["load-cmd-usage"]));
						return true;
					}
				case "delmap":
					if(!$this->plugin->isManager($sender) or !$sender->isOp())
					{
						$sender->sendMessage($this->plugin->lang["delmap-ban-msg"]);
						return true;
					}
					if(!isset($args[1]))
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["delmap-usage"]));
						return true;
					}
					else
					{
						if(!isset($args[2]))
						{
							$sender->sendMessage("...");
							return true;
						}
						elseif($args[2] == "yes")
						{
							if($this->plugin->getServer()->isLevelGenerated($args[1]) && !$this->plugin->getServer()->isLevelLoaded($args[1]))
							{
								$like=$this->plugin->delMapData("worlds/".$args[1]);
								if($like === true)
								{
									$sender->sendMessage(str_replace("{world}",$args[1],$this->plugin->lang["delmap-success-msg"]));
								}
								else
								{
									$sender->sendMessage(str_replace("{world}",$args[1],$this->plugin->lang["delmap-fail-msg"]));
								}
								return true;
							}
							else
							{
								if($this->plugin->getServer()->isLevelLoaded($args[1]))
								{
									$level = $this->plugin->getServer()->getLevelbyName($args[1]);
									$ok = $this->plugin->getServer()->unloadLevel($level); 
									$like=$this->plugin->delMapData("worlds/".$args[1]);
									if($like === true)
									{
										$sender->sendMessage(str_replace("{world}",$args[1],$this->plugin->lang["delmap-success-msg"]));
									}
									else
									{
										$sender->sendMessage(str_replace("{world}",$args[1],$this->plugin->lang["delmap-fail-msg"]));
									}
									return true;
								}
								$sender->sendMessage($this->plugin->lang["delmap-useless-msg"]);
								return true;
							}
						}
						else
						{
							$sender->sendMessage("...");
							return true;
						}
					}
					return true;
				case "unload":
					if(isset($args[1]))
					{
						$l = $args[1];
						if(!$this->plugin->getServer()->isLevelLoaded($l)) 
						{
							$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["unload-failed-not-loaded-msg"]));
						}
						else 
						{
							$level = $this->plugin->getServer()->getLevelbyName($l);
							$ok = $this->plugin->getServer()->unloadLevel($level); 
							if($ok !== true)
							{
								$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["unload-failed-msg"]));
							}
							else
							{
								$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["unload-success-msg"]));
							}
						}
						return true;
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["unload-cmd-usage"]));
						return true;
					}
				case "banpvp":
					if(isset($args[1]))
					{
						$list=$this->plugin->config->get("banpvp-world");
						if(in_array($args[1],$list))
						{
							$inv = array_search($args[1],$list);
							array_splice($list, $inv, 1); 
							$this->plugin->config->set("banpvp-world",$list);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{level}",$args[1],$this->plugin->lang["del-banpvp-msg"]));
							return true;
						}
						else
						{
							$list[]=$args[1];
							$this->plugin->config->set("banpvp-world",$list);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{level}",$args[1],$this->plugin->lang["add-banpvp-msg"]));
							return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["banpvp-cmd-usage"])); 
						return true;
					}
				case "oppvp":
					if(isset($args[1]))
					{
						switch($args[1])
						{
							case "true":
								$this->plugin->config->set("allow-op-pvp",true);
								$this->plugin->config->save();
								$sender->sendMessage($this->plugin->lang["allow-op-pvp-msg"]);
								return true;
							case "false":
								$this->plugin->config->set("allow-op-pvp",false);
								$this->plugin->config->save();
								$sender->sendMessage($this->plugin->lang["disallow-op-pvp-msg"]);
								return true;
							default:
								$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["oppvp-cmd-usage"]));
								return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["oppvp-cmd-usage"]));
						return true; 
					}
				case "lockgm":
					if(isset($args[1]) && isset($args[2]))
					{
						$worldname=$args[1];
						switch($args[2])
						{
							case "0":
								$t=$this->plugin->config->get("lock-gm-world");
								$t[$worldname]=0;
								$this->plugin->config->set("lock-gm-world",$t);
								$this->plugin->config->save();
								$sender->sendMessage(str_replace("{level}",$worldname,$this->plugin->lang["set-gm-survival-msg"]));
								return true;
							case "1":
								$t=$this->plugin->config->get("lock-gm-world");
								$t[$worldname]=1;
								$this->plugin->config->set("lock-gm-world",$t);
								$this->plugin->config->save();
								$sender->sendMessage(str_replace("{level}",$worldname,$this->plugin->lang["set-gm-creative-msg"]));
								return true;
							case "remove":
								$t=$this->plugin->config->get("lock-gm-world");
								unset($t[$worldname]);
								$this->plugin->config->set("lock-gm-world",$t);
								$this->plugin->config->save();
								$sender->sendMessage(str_replace("{level}",$worldname,$this->plugin->lang["set-gm-remove-msg"]));
								return true;
							default:
								$sender->sendMessage(str_replace("%n","\n",str_replace("{cmd}",$this->cmd,$this->plugin->lang["lockgm-cmd-usage"])));
								return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("%n","\n",str_replace("{cmd}",$this->cmd,$this->plugin->lang["lockgm-cmd-usage"])));
						return true;
					}
				case "language":
					if(isset($args[1]))
					{
						switch($args[1])
						{
							case "eng":
							case "english":
								$this->plugin->lang=$this->plugin->loadLanguage("eng");
								$this->plugin->config->set("language","eng");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] Successfully set your language to English!");
								return true;
							case "ch":
							case "中文简体":
								$this->plugin->lang=$this->plugin->loadLanguage("ch");
								$this->plugin->config->set("language","ch");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] 成功设置语言为 中文简体！");
								return true;
							case "cho":
							case "中文繁體":
								$this->plugin->lang=$this->plugin->loadLanguage("cho");
								$this->plugin->config->set("language","cho");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] 成功設定語言為 中文繁體！");
								return true;
							case "de":
							case "deutech":
								$this->plugin->lang=$this->plugin->loadLanguage("de");
								$this->plugin->config->set("language","de");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] Die Sprache IST Deutsch！");
								return true;
							case "jp":
							case "日本語":
								$this->plugin->lang=$this->plugin->loadLanguage("jp");
								$this->plugin->config->set("language","jp");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] 言葉を日本語にすることに成功する！");
								return true;
							case "fr":
							case "français":
								$this->plugin->lang=$this->plugin->loadLanguage("fr");
								$this->plugin->config->set("language","fr");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] La langue est le français！");
								return true;
							case "kr":
							case "한국어":
								$this->plugin->lang=$this->plugin->loadLanguage("kr");
								$this->plugin->config->set("language","kr");
								$this->plugin->config->save();
								$sender->sendMessage("§a[CrazyWorld] 성공 설정 언어 조선어！");
								return true;
							default:
								$sender->sendMessage(str_replace("%n","\n",str_replace("{cmd}",$this->cmd,$this->plugin->lang["language-cmd-usage"])));
								return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("%n","\n",str_replace("{cmd}",$this->cmd,$this->plugin->lang["language-cmd-usage"])));
						return true;
					}
				case "fixname":
					$levels = $this->plugin->getServer()->getLevels();
					foreach ($levels as $level)
					{
						$level->getProvider()->getLevelData()->LevelName = new StringTag("LevelName", $level->getFolderName());
					}
					$sender->sendMessage($this->plugin->lang["fixname-success"]);
					return true;
				case "setpos":
					if(isset($args[1]))
					{
						$posdata=$this->plugin->position->getAll();
						$posname=$args[1];
						if(isset($posdata[$posname]))
						{
							if(!isset($args[2]))
							{
								$sender->sendMessage(str_replace("{cmd}",$this->cmd,str_replace("{name}",$posname,$this->plugin->lang["pos-exists-msg"])));
								return true;
							}
							elseif($args[2] == "remove")
							{
								$this->plugin->position->remove($posname);
								$this->plugin->position->save();
								$sender->sendMessage(str_replace("{name}",$posname,$this->plugin->lang["remove-pos-success-msg"]));
								return true;
							}
							else
							{
								$sender->sendMessage(str_replace("{cmd}",$this->cmd,str_replace("{name}",$posname,$this->plugin->lang["pos-exists-msg"])));
								return true;
							}
						}
						else
						{
							$x=(int)$sender->x;$y=(int)$sender->y;$z=(int)$sender->z;$level=$sender->level->getFolderName();
							$locat=$x.":".$y.":".$z.":".$level;
							$this->plugin->position->set($posname,$locat);
							$this->plugin->position->save();
							$sender->sendMessage(str_replace("{name}",$posname,$this->plugin->lang["add-pos-success-msg"]));
							return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["set-pos-cmd-usage"]));
						return true;
					}
				case "whitelist":
					if(isset($args[1]))
					{
						$list=$this->plugin->config->get("white-list-world");
						if(in_array($args[1],$list))
						{
							$inv = array_search($args[1],$list);
							array_splice($list, $inv, 1); 
							$this->plugin->config->set("white-list-world",$list);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{level}",$args[1],$this->plugin->lang["del-whitelist-msg"]));
							return true;
						}
						else
						{
							$list[]=$args[1];
							$this->plugin->config->set("white-list-world",$list);
							$this->plugin->config->save();
							$sender->sendMessage(str_replace("{level}",$args[1],$this->plugin->lang["add-whitelist-msg"]));
							return true;
						}
					}
					else
					{
						$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["whitelist-cmd-usage"])); 
						return true;
					}
				default:
					$this->cmdHelp=str_replace("{cmd}",$this->cmd,$this->plugin->lang["main-help"]);
					$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
					$sender->sendMessage($this->cmdHelp);
					return true;
			}
		}
		else
		{
			$this->cmdHelp=str_replace("{cmd}",$this->cmd,$this->plugin->lang["main-help"]);
			$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
			$sender->sendMessage($this->cmdHelp);
			return true;
		}
	}
}