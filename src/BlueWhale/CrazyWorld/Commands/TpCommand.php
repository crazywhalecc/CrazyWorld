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
use pocketmine\level\Position;

class TpCommand extends Command
{
	private $plugin;
	
	public function __construct(CrazyWorld $plugin)
	{
		$desc=$plugin->command->get("TpCommand");
		parent::__construct($desc["command"], $desc["description"]);
		$permission=($desc["permission"] == "true") ? "cw.command.true" : ($desc["permission"] == "op" ? "cw.command.op" : "cw.command.op");
		$this->setPermission($permission);
		$this->plugin = $plugin;
		$this->cmd=$desc["command"];
		$c=$this->cmd;
		$this->cmdHelp=str_replace("{cmd}",$c,$this->plugin->lang["tp-cmd-usage"]);
		$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
	}
	public function execute(CommandSender $sender, $label, array $args)//Command Executer
	{
		if(!$this->plugin->isEnabled())
			return false;
		if($sender instanceof ConsoleCommandSender)
		{
			$sender->sendMessage($this->plugin->lang["player-use-only-msg"]);
			return true;
		}
		if(isset($args[0]))
		{
			$posname=$args[0];
			$list=$this->plugin->position->getAll();
			if(in_array($posname,$list))
			{
				$posdata=explode($list[$posname]);
				$level=$this->plugin->getServer()->getLevelByName($posdata[3]);
				if($level !== null)
				{
					$result=$sender->teleport(new Position($posdata[0],$posdata[1],$posdata[2],$level));
					if($result)
					{
						$sender->sendMessage(str_replace("{name}",$posname,$this->plugin->lang["teleport-msg"]));
					}
					else
					{
						$sender->sendMessage(str_replace("{name}",$posname,$this->plugin->lang["teleport-failed-msg"]));
					}
					return true;
				}
				else
				{
					$sender->sendMessage(str_replace("{name}",$posname,$this->plugin->lang["teleport-failed-msg"]));
					return true;
				}
			}
			else
			{
				if($args[0] == "list")
				{
					$sender->sendMessage($this->plugin->lang["tp-list-msg"]);
					$namelist=[];
					foreach($list as $na=>$ba)
					{
						$namelist[]=$na;
					}
					$sender->sendMessage("§b".implode(", ",$namelist));
					return true;
				}
				$sender->sendMessage(str_replace("{name}",$posname,$this->plugin->lang["pos-not-exist-msg"]));
				return true;
			}
		}
		else
		{
			$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["tp-cmd-usage"]));
			return true;
		}
	}
}