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

use BlueWhale\CrazyWorld\CrazyWorld;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;

class EventListener implements Listener
{
	private $plugin;
	
	public function __construct(CrazyWorld $plugin)
	{
		$plugin->getServer()->getPluginManager()->registerEvents($this,$plugin);
		$this->plugin=$plugin;
	}
	public function onTouch(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$user = $player->getName();
		$itemid = $event->getItem()->getID();
		$itemtouch = $this->plugin->config->get("item-touch");
		if(in_array($itemid,$itemtouch))
			$this->checkPerm($event);
	}
	public function playerblockBreak(BlockBreakEvent $event) {$this->checkPerm($event);}
	public function PlayerPlaceBlock(BlockPlaceEvent $event) {$this->checkPerm($event);}
	public function onHurt(EntityDamageEvent $eventp)
	{
		if($eventp instanceof EntityDamageByEntityEvent)
		{
			$this->checkPvP($eventp);
		}
	}
	public function onTp(EntityTeleportEvent $event)
	{
		if($event->getEntity() instanceof Player)
		{
			$player=$event->getEntity();
			$target=$event->getTo();
			$level=$target->level->getFolderName();
			$data=$this->plugin->config->get("white-list-world");
			if(in_array($level,$data))
			{
				if($this->plugin->isManager($player))
				{
					$i=1;
				}
				else
				{
					$event->setCancelled(true);
					$player->sendMessage($this->plugin->lang["no-perm-enter-msg"]);
				}
			}
		}
	}
	public function onLevelChange(EntityLevelChangeEvent $event)
	{
		$player=$event->getEntity();
		if($player instanceof Player)
		{
			$target=$event->getTarget()->getFolderName();
			$list=$this->plugin->config->get("lock-gm-world");
			if(isset($list[$target]))
			{
				if($this->plugin->isManager($player))
				{
					$i=1;
				}
				else
				{
					$current=$player->getGamemode();
					if($current != $list[$target])
					{
						$player->setGamemode($list[$target],true);
					}
				}
			}
		}
	}
	public function gamemodeChange(PlayerGameModeChangeEvent $event)
	{
		$player=$event->getPlayer();
		$level=$player->level->getFolderName();
		$newGm=$event->getNewGamemode();
		$list=$this->plugin->config->get("lock-gm-world");
		if(isset($list[$level]))
		{
			if($newGm != $list[$level])
			{
				if(!$this->plugin->isManager($player))
				{
					$event->setCancelled(true);
				}
			}
		}
	}
	public function checkPerm($event)
	{
		$player = $event->getPlayer();
		$user = $player->getName();
		$level = $player->getLevel()->getFolderName();
		$pw=$this->plugin->config->get("protect-world");
		$admin=$this->plugin->config->get("admin");
		$msg=$this->plugin->lang["protect-msg"];
		if((in_array($level,$pw)) and (!in_array($user,$admin)))
		{
			$this->plugin->sendMsgPacket($player,$msg,$this->plugin->config->get("protect-msg-type"));
			$event->setCancelled(true);
		}
	}
	public function checkPvP($eventp)
	{
		if(($eventp->getDamager() instanceof Player) && ($eventp->getEntity() instanceof Player))
		{
			$level=$eventp->getDamager()->getLevel()->getFolderName();
			$isop=$eventp->getDamager()->isOp() ? "yes" : "no";
			if(in_array($level,$this->plugin->config->get("banpvp-world")))
			{
				if($isop == "yes" && $this->plugin->config->get("allow-op-pvp")==false)
				{
					$eventp->setCancelled(true);
					switch($this->plugin->config->get("banpvp-msg-type"))
					{
						case 1:
							$eventp->getDamager()->sendTip($this->plugin->lang["banpvp-msg"]);
							break;
						case 2:
							$eventp->getDamager()->sendPopup($this->plugin->lang["banpvp-msg"]);
							break;
						default:
							$eventp->getDamager()->sendMessage($this->plugin->msgs($this->plugin->lang["banpvp-msg"],$eventp->getEntity()));
							break;
					}
					return;
				}
				elseif($isop == "no")
				{
					$eventp->setCancelled(true);
					switch($this->plugin->config->get("banpvp-msg-type"))
					{
						case 1:
							$eventp->getDamager()->sendTip($this->plugin->lang["banpvp-msg"]);
							break;
						case 2:
							$eventp->getDamager()->sendPopup($this->plugin->lang["banpvp-msg"]);
							break;
						default:
							$eventp->getDamager()->sendMessage($this->plugin->msgs($this->plugin->lang["banpvp-msg"],$eventp->getEntity()));
							break;
					}
					return;
				}
			}
		}
	}
}