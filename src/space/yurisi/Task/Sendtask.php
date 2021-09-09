<?php
declare(strict_types=1);

namespace space\yurisi\Task;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\scheduler\Task;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

use onebone\economyapi\EconomyAPI;

use nexuscore\nexuscore;

use space\yurisi\PlayerInfoScoreBoard;

class Sendtask extends Task{

	public function onRun($tick){
        $wipehour=0;
        $wipemin=0;
        $wipesec=0;
        $wipehour=(nexuscore::$nextwipe - nexuscore::$nextwipe % (60*60)) / (60*60);
        $wipemin=(nexuscore::$nextwipe - (nexuscore::$nextwipe - ($wipehour*60*60)) % 60) / 60;
        if($wipemin === 60) $wipemin = 0;
        $wipesec=nexuscore::$nextwipe-(($wipehour*60*60)+($wipemin*60));
        if($wipehour===0&&$wipemin===0){
          $wipecount = $wipesec."秒";
        }else if($wipehour===0&&$wipemin!==0){
          $wipecount = $wipemin."分".$wipesec."秒";
        }else{
          $wipecount = $wipehour."時間".$wipemin."分".$wipesec."秒";
        }
		foreach(Server::getInstance()->getOnlinePlayers() as $player) {
			$name = $player->getName();
			if(PlayerInfoScoreBoard::getInstance()->isOn($player)) {
				$this->RemoveData($player);
				$this->setupData($player);
				$this->sendData($player,"§eユーザー名: ".$player->getName(),1);
				$this->sendData($player,"§e所持金: ".EconomyAPI::getInstance()->getMonetaryUnit().EconomyAPI::getInstance()->myMoney($name),2);
				$this->sendData($player,"§b座標: ".$player->getfloorX().",".$player->getfloorY().",".$player->getfloorZ(),3);
				$this->sendData($player,"§bワールド: ".$player->getLevel()->getFolderName(),4);
				$this->sendData($player,"§c現在時刻: ".date("G時i分s秒"),5);
				$this->sendData($player,"§6持ってるid: ".$player->getInventory()->getItemInHand()->getId().":".$player->getInventory()->getItemInHand()->getDamage(),6);
				$this->sendData($player,"§6オンライン人数: ".count(Server::getInstance()->getOnlinePlayers())."/".Server::getInstance()->getMaxPlayers(),7);
				$this->sendData($player,"§c残シールド値: ".nexuscore::$shieldconfig->get($player->getName()),8);
				$this->sendData($player,"§c次の掃除まで: ".$wipecount,9);
				$this->sendData($player,"§6PING: ".$player->getPing()."ms",10);
				continue;
			}
			$this->RemoveData($player);
		}
	}

	private function setupData(Player $player){
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = "sidebar";
		$pk->displayName = "§aNexus server β";
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$player->sendDataPacket($pk);
	}

	private function sendData(Player $player,String $data,Int $id){
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "sidebar";
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $data;
		$entry->score = $id;
		$entry->scoreboardId = $id+11;
		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->sendDataPacket($pk);
	}

	private function RemoveData(Player $player){
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = "sidebar";
		$player->sendDataPacket($pk);
	}
}