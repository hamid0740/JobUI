<?php

/*
 * EconomyS, the massive economy plugin with many features for PocketMine-MP
 * Copyright (C) 2013-2015  onebone <jyc00410@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 /*
 * The original EconomyJob is written by OneBone
 * added the UI for it by hamid0740
 * Discord: hamid0740#3725
 * Github: <https://github.com/hamid0740/>
 * Telegram & Instagram: @hamid0740
 */

namespace hamid0740\JobUI;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

use onebone\economyapi\EconomyAPI;

class JobUI extends PluginBase implements Listener{
	/** @var Config */
	private $jobs;
	/** @var Config */
	private $player;

	/** @var  EconomyAPI */
	private $api;

	/** @var EconomyJobUI   */
	private static $instance;
	
	
	
	public function registerSubcommands(){
		$this->subcommands["retire"] = new RetireSubcommand;
		$this->subcommands["ui"] = new UiSubcommand;
	}

	public function onEnable(){
		@mkdir($this->getDataFolder());
		if(!is_file($this->getDataFolder()."jobs.yml")){
			$this->jobs = new Config($this->getDataFolder()."jobs.yml", Config::YAML, yaml_parse($this->readResource("jobs.yml")));
		}else{
			$this->jobs = new Config($this->getDataFolder()."jobs.yml", Config::YAML);
		}
		$this->player = new Config($this->getDataFolder()."players.yml", Config::YAML);

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->api = EconomyAPI::getInstance();
		self::$instance = $this;
	}

	private function readResource($res){
		$path = $this->getFile()."resources/".$res;
		$resource = $this->getResource($res);
		if(!is_resource($resource)){
			$this->getLogger()->debug("Tried to load unknown resource ".TextFormat::AQUA.$res.TextFormat::RESET);
			return false;
		}
		$content = stream_get_contents($resource);
		@fclose($content);
		return $content;
	}

	public function onDisable(){
		$this->player->save();
	}

	/**
	 * @priority LOWEST
	 * @ignoreCancelled true
	 * @param BlockBreakEvent $event
	 */
	public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();

		$job = $this->jobs->get($this->player->get($player->getName()));
		if($job !== false){
			if(isset($job[$block->getID().":".$block->getDamage().":break"])){
				$money = $job[$block->getID().":".$block->getDamage().":break"];
				if ($player->hasPermission("jobui.earn.break")) {
					if($money > 0){
						$this->api->addMoney($player, $money);
						$player->sendPopup("§b+ Money for Job");
					}else{
						$this->api->reduceMoney($player, $money);
					}
				}else{
					$player->sendPopup("§cYou can't earn money by a Job, use /retire to be retired");
				}
			}
		}
	}

	/**
	 * @priority LOWEST
	 * @ignoreCancelled true
	 * @param BlockPlaceEvent $event
	 */
	public function onBlockPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();

		$job = $this->jobs->get($this->player->get($player->getName()));
		if($job !== false){
			if(isset($job[$block->getID().":".$block->getDamage().":place"])){
				$money = $job[$block->getID().":".$block->getDamage().":place"];
				if ($player->hasPermission("jobui.earn.place")) {
					if($money > 0){
						$this->api->addMoney($player, $money);
						$player->sendPopup("§b+ Money for Job");
					}else{
						$this->api->reduceMoney($player, $money);
					}
				}else{
					$player->sendPopup("§cYou can't earn money by a Job, use /retire to be retired");
				}
			}
		}
	}

	/**
	 * @return EconomyJobUI
	*/
	public static function getInstance(){
		return static::$instance;
	}

	/**
	 * @return array
	 */
	public function getJobs(){
		return $this->jobs->getAll();
	}

	/**
	 * @return array
	 *
	 */
	public function getPlayers(){
		return $this->player->getAll();
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $params) : bool{
		if($command->getName() === "job"){
			if(!$sender instanceof Player){
				$sender->sendMessage("Please run this command in-game.");
				return true;
			}else{
				if ($sender->hasPermission("jobui.command.job")) {
					$this->FormJob($sender);
					return true;
				}else{
					$sender->sendMessage("§7[§6Jobs§7] §cYou can join the job only in the specified world");
					return false;
				}
			}
		}
		if($command->getName() === "retire"){
			if(!$sender instanceof Player){
				$sender->sendMessage("Please run this command in-game.");
				return true;
			}else{
				if ($sender->hasPermission("jobui.command.retire")) {
					if($this->player->exists($sender->getName())){
						$job = $this->player->get($sender->getName());
						$this->player->remove($sender->getName());
						$sender->sendMessage("§7[§6Jobs§7] §cYou have retired from the job§e \"$job\"");
						return true;
					}else{
						$sender->sendMessage("§7[§6Jobs§7] §cYou don't have a job yet. First join a job then you'll be able to be retired.");
						return true;
					}
				}else{
					$sender->sendMessage("§7[§6Jobs§7] §cYou can join the job only in the specified world");
					return false;
				}
			}
		}
	}
	
	public function FormJob($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
				}
				switch($result){
					case "0";
					$this->FormJobJoin($player);
					break;
					
					case "1";
					$this->FormInfo($player);
					break;
					
					case "2";
					$player->sendMessage("§7[§6Jobs§7] §aYour job is: §e".$this->player->get($player->getName()));
					break;
					
					case "3";
					if($this->player->exists($player->getName())){
						$job = $this->player->get($player->getName());
						$this->player->remove($player->getName());
						$player->sendMessage("§7[§6Jobs§7] §cYou have retired from the job§e \"$job\"");
					}else{
						$player->sendMessage("§7[§6Jobs§7] §cYou don't have a job yet. First join a job then you'll be able to be retired.");
					}
					break;
				}
			});
			$form->setTitle("§bJob");
			$form->setContent("§aYour Job: §e".$this->player->get($player->getName()));
			$form->addButton("§aJoin a Job");
			$form->addButton("§6Jobs Info");
			$form->addButton("§dMy Job");
			$form->addButton("§cRetire from the Job");
			$form->sendToPlayer($player);
			return $form;
	}
	
	public function FormJobJoin($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
				}
				switch($result){
					case "0";
					$this->player->set($player->getName(), "Tree-Cutter");
					$player->sendMessage("§7[§6Jobs§7] §aYou have joined the job §eTree-Cutter");
					break;
					
					case "1";
					$this->player->set($player->getName(), "Miner");
					$player->sendMessage("§7[§6Jobs§7] §aYou have joined the job §eMiner");
					break;
					
				}
			});
			$form->setTitle("§aJobs List");
			$form->addButton("§aTree Cutter", 1, "https://gamepedia.cursecdn.com/minecraft_gamepedia/c/c5/Oak_Log_Axis_Y_JE5_BE3.png");
			$form->addButton("§bMiner", 1, "https://gamepedia.cursecdn.com/minecraft_gamepedia/0/0c/Iron_Ore_JE3.png");
			$form->sendToPlayer($player);
			return $form;
	}
	
	public function FormInfo($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, $data = null){
		$result = $data[0];
					
		if($result === null){
			return true;
		}
			switch($result){
				case 0:
				break;
			}
		});
		$form->setTitle("§aJobs Info");
		$form->setContent("§6- §aTree Cutter§6:\n§bOak wood §d[25$] §bSpruce wood §d[25$]\n§bBirch wood §d[25$] §bJungle wood §d[25$]\n§bAcacia wood §d[25$] §bDark Oak wood §d[25$]\n§6- §aMiner§6:\n§bStone §d[25$] §bCoal ore §d[30$]\n§bIron ore §d[35$]");
		$form->addButton("§aOkay!");	
		$form->sendToPlayer($player);
	}
}
