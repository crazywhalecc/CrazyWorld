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
use pocketmine\command\ConsoleCommandSender;

class WorldTpCommand extends Command
{
	private $plugin;
	
	public function __construct(CrazyWorld $plugin)
	{
		$desc=$plugin->command->get("WorldTpCommand");
		parent::__construct($desc["command"], $desc["description"]);
		$permission=($desc["permission"] == "true") ? "cw.command.true" : ($desc["permission"] == "op" ? "cw.command.op" : "cw.command.op");
		$this->setPermission($permission);
		$this->plugin = $plugin;
		$this->cmd=$desc["command"];
		$c=$this->cmd;
		$this->cmdHelp=str_replace("{cmd}",$c,$this->plugin->lang["world-tp-help"]);
		$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
	}
	public function execute(CommandSender $sender, $label, array $args)//Command Executer
	{
		if(!$this->plugin->isEnabled())
			return false;
		if(isset($args[0]))
		{
			$l = $args[0];
			if($l == "list")
			{
				$sender->sendMessage("§6=====WorldList=====");
				$levels = $this->plugin->getServer()->getLevels();
				foreach ($levels as $level)
				{
				    $name[]=$level->getFolderName();
				}
				$sender->sendMessage("§b".implode(", ",$name));
				return true;
			}
			if($sender instanceof ConsoleCommandSender)
			{
				$sender->sendMessage($this->plugin->lang["player-use-only-msg"]);
				return true;
			}
			if($this->plugin->getServer()->isLevelLoaded($l)) 
			{
				$r=$sender->teleport($this->plugin->getServer()->getInstance()->getLevelByName($l)->getSafeSpawn());
				if($r !== false)
					$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["world-tp-msg"]));
				return true;
			}
			else
			{
				$sender->sendMessage(str_replace("{level}",$l,$this->plugin->lang["not-exist-world-msg"]));
				return true;
			}
		}
		else
		{
			$sender->sendMessage($this->cmdHelp);
			return true;
		}
	}
}