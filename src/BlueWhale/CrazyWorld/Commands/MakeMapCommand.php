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
use pocketmine\level\generator\Generator;

class MakeMapCommand extends Command
{
	private $plugin;
	
	public function __construct(CrazyWorld $plugin)
	{
		$desc=$plugin->command->get("MakeMapCommand");
		parent::__construct($desc["command"], $desc["description"]);
		$permission=($desc["permission"] == "true") ? "cw.command.true" : ($desc["permission"] == "op" ? "cw.command.op" : "cw.command.op");
		$this->setPermission($permission);
		$this->plugin = $plugin;
		$this->cmd=$desc["command"];
		$c=$this->cmd;
		$this->cmdHelp=str_replace("{cmd}",$c,$this->plugin->lang["makemap-help"]);
		$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
	}
	public function execute(CommandSender $sender, $label, array $args)//Command Executer
	{
		if(!$this->plugin->isEnabled())
			return false;
		if(!$this->plugin->isManager($sender) && $sender->isOp())
		{
			$sender->sendMessage(str_replace("%n","\n",str_replace("{cmd}",$this->plugin->command->get("MainCommand")["command"],$this->plugin->lang["not-admin-op-msg"])));
			return true;
		}
		elseif(!$this->plugin->isManager($sender) && !$sender->isOp())
		{
			$sender->sendMessage($this->plugin->lang["not-admin-player-msg"]);
			return true;
		}
		if(isset($args[0]))
		{
			$name=$args[0];
			if($this->plugin->getServer()->isLevelGenerated($name))
			{
				$sender->sendMessage(str_replace("{level}",$name,$this->plugin->lang["makemap-failed-exists-msg"]));
				return true;
			}
			if(isset($args[1]))
			{
				switch($args[1])
				{
					case "default":
								if(isset($args[2]))
								{
									$seed=$args[2];
									$opts=[];
									$gen=Generator::getGenerator("default");
									$typename="default";
									$sender->sendMessage(str_replace("{type}",$typename,str_replace("{level}",$name,$this->plugin->lang["makemap-making-msg"])));
									$this->plugin->getServer()->generateLevel($name,$seed,$gen,$opts);
									$this->plugin->getServer()->loadLevel($name);
									$sender->sendMessage($this->plugin->lang["makemap-success-msg"]);
									return true;
								}
								else
								{$sender->sendMessage(str_replace("{cmd}",$this->cmd,$this->plugin->lang["makemap-deafult-cmd-usage"]));return true;}
					case "flat":
								if(isset($args[2])){$opts=$args[2];}
								else{$opts=[];}
								$seed=1;
								$gen=Generator::getGenerator("Flat");
								$typename="Flat";
								$sender->sendMessage(str_replace("{type}",$typename,str_replace("{level}",$name,$this->plugin->lang["makemap-making-msg"])));
								$this->plugin->getServer()->generateLevel($name,$seed,$gen,$opts);
								$this->plugin->getServer()->loadLevel($name);
								$sender->sendMessage($this->plugin->lang["makemap-success-msg"]);
								return true;
					case "empty":
								if(isset($args[2])){$tsp=$this->temp->get("empty-type");$opts=$tsp;}
								else{$opts=[];}
								$seed=1;
								$gen=Generator::getGenerator("empty");
								$typename="empty";
								$sender->sendMessage(str_replace("{type}",$typename,str_replace("{level}",$name,$this->plugin->lang["makemap-making-msg"])));
								$this->plugin->getServer()->generateLevel($name,$seed,$gen,$opts);
								$this->plugin->getServer()->loadLevel($name);
								$sender->sendMessage($this->plugin->lang["makemap-success-msg"]);
								return true;	
					case "land":
								$opts=[];
								$seed=1;
								$gen=Generator::getGenerator("land");
								$typename="land";
								$sender->sendMessage(str_replace("{type}",$typename,str_replace("{level}",$name,$this->plugin->lang["makemap-making-msg"])));
								$this->plugin->getServer()->generateLevel($name,$seed,$gen,$opts);
								$this->plugin->getServer()->loadLevel($name);
								$sender->sendMessage($this->plugin->lang["makemap-success-msg"]);
								return true;
					case "snowland":
								$opts=[];
								$seed=1;
								$gen=Generator::getGenerator("snowland");
								$typename="snowland";
								$sender->sendMessage(str_replace("{type}",$typename,str_replace("{level}",$name,$this->plugin->lang["makemap-making-msg"])));
								$this->plugin->getServer()->generateLevel($name,$seed,$gen,$opts);
								$this->plugin->getServer()->loadLevel($name);
								$sender->sendMessage($this->plugin->lang["makemap-success-msg"]);
								return true;
					default:
								$m=$this->plugin->setNewLevel($name,$args[1]);
								if($m === true)
								{
									$sender->sendMessage($this->plugin->lang["makemap-success-msg"]);
								}
								elseif($m === 1)
								{
									$sender->sendMessage($this->plugin->lang["makemap-failed-unknown-generator-msg"]);
								}
								elseif($m === false)
								{
									$sender->sendMessage(str_replace("{level}",$name,$this->plugin->lang["makemap-failed-exists-msg"]));
								}
								return true;
				}
			}
			else
			{
				$this->cmdHelp=str_replace("{cmd}",$this->cmd,$this->plugin->lang["makemap-help"]);
				$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
				$sender->sendMessage($this->cmdHelp);
				return true;
			}
		}
		else
		{
			$this->cmdHelp=str_replace("{cmd}",$this->cmd,$this->plugin->lang["makemap-help"]);
			$this->cmdHelp=str_replace("%n","\n",$this->cmdHelp);
			$sender->sendMessage($this->cmdHelp);
			return true;
		}
	}
}