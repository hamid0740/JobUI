<?php

	/*
	* The original EconomyJob is written by OneBone
	* Used the FormAPI Library virion by jojoe77777
	* Added the UI and make it Advanced by hamid0740
	* Discord: hamid0740#3725
	* Github: https://github.com/hamid0740/
	* Poggit: https://poggit.pmmp.io/p/JobUI
	* Telegram & Instagram: @hamid0740
	*/

namespace hamid0740\JobUI;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\Animal;
use pocketmine\entity\Monster;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

use jojoe77777\FormAPI;
use jojoe77777\FormAPI\SimpleForm;

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener{

	/** @var Config */
	private $jobs;
	/** @var Config */
	private $player;
	/** @var Config */
	private $messages;

	/** @var  EconomyAPI */
	private $api;

	/** @var Main */
	private static $instance;

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->saveResource("jobs.yml");
		$this->saveResource("messages.yml");
		$this->jobs = new Config($this->getDataFolder() . "jobs.yml", Config::YAML);
		$this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
		$this->player = new Config($this->getDataFolder() . "players.yml", Config::YAML);

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
	* Get JobUI messages
	*
	* @param string $id
	*
	* @return string | bool
	*/
	public function getMessage($id){
		if($this->messages->exists($id)){
			return $this->messages->get($id);
		}
		return false;
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
			if(isset($job["Mission"][$block->getID().":".$block->getDamage().":Break"])){
				$money = $job["Mission"][$block->getID().":".$block->getDamage().":Break"];
				if ($player->hasPermission("jobui.earn.break") or $player->hasPermission("jobui.*")) {
					if($money > 0){
						$this->api->addMoney($player, $money);
						$player->sendPopup($this->getMessage("earn-popup-1") . $money . EconomyAPI::getInstance()->getMonetaryUnit() . $this->getMessage("earn-popup-2"));
					}else{
						$this->api->reduceMoney($player, $money);
					}
				}else{
					$player->sendPopup($this->getMessage("break-noperm-popup"));
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
			if(isset($job["Mission"][$block->getID().":".$block->getDamage().":Place"])){
				$money = $job["Mission"][$block->getID().":".$block->getDamage().":Place"];
				if ($player->hasPermission("jobui.earn.place") or $player->hasPermission("jobui.*")) {
					if($money > 0){
						$this->api->addMoney($player, $money);
						$player->sendPopup($this->getMessage("earn-popup-1") . $money . EconomyAPI::getInstance()->getMonetaryUnit() . $this->getMessage("earn-popup-2"));
					}else{
						$this->api->reduceMoney($player, $money);
					}
				}else{
					$player->sendPopup($this->getMessage("place-noperm-popup"));
				}
			}
		}
	}
	
	/**
	 * @priority LOWEST
	 * @ignoreCancelled true
	 * @param EntityDeathEvent $event
	 */
	public function onMobDeath(EntityDeathEvent $event){
		$entity = $event->getEntity();
		$cause = $entity->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			$player = $cause->getDamager();
			if($player instanceof Player){
				$job = $this->jobs->get($this->player->get($player->getName()));
				if($job !== false){
					if(!$entity instanceof Player){
						if(isset($job["Mission"]["Hunter"])){
							$money = $job["Mission"]["Hunter"];
							if ($player->hasPermission("jobui.earn.hunter") or $player->hasPermission("jobui.*")) {
								if($money > 0){
									$this->api->addMoney($player, $money);
									$player->sendPopup($this->getMessage("earn-popup-1") . $money . EconomyAPI::getInstance()->getMonetaryUnit() . $this->getMessage("earn-popup-2"));
								}else{
									$this->api->reduceMoney($player, $money);
								}
							}else{
								$player->sendPopup($this->getMessage("hunter-noperm-popup"));
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * @priority LOWEST
	 * @ignoreCancelled true
	 * @param PlayerDeathEvent $event
	 */
	public function onPlayerDeath(PlayerDeathEvent $event){
		$entity = $event->getEntity();
		$cause = $entity->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			$player = $cause->getDamager();
			if($player instanceof Player){
				$job = $this->jobs->get($this->player->get($player->getName()));
				if($job !== false){
					if($entity instanceof Player){
						if(isset($job["Mission"]["Murderer"])){
							$money = $job["Mission"]["Murderer"];
							if ($player->hasPermission("jobui.earn.murderer") or $player->hasPermission("jobui.*")) {
								if($money > 0){
									$this->api->addMoney($player, $money);
									$player->sendPopup($this->getMessage("earn-popup-1") . $money . EconomyAPI::getInstance()->getMonetaryUnit() . $this->getMessage("earn-popup-2"));
								}else{
									$this->api->reduceMoney($player, $money);
								}
							}else{
								$player->sendPopup($this->getMessage("murderer-noperm-popup"));
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * @return Main
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
			}else{
				if ($sender->hasPermission("jobui.command.job") or $player->hasPermission("jobui.*")) {
					$this->FormJob($sender);
				}else{
					$sender->sendMessage("§7[§6JobUI§7] " . "§cYou can't join a job in this world");
				}
			}
		}
		if($command->getName() === "retire"){
			if(!$sender instanceof Player){
				$sender->sendMessage("Please run this command in-game.");
			}else{
				if ($sender->hasPermission("jobui.command.retire") or $player->hasPermission("jobui.*")){
					if($this->player->exists($sender->getName())){
						$job = $this->player->get($sender->getName());
						$this->player->remove($sender->getName());
						$sender->sendMessage("§7[§6JobUI§7] " . $this->getMessage("retire-message") . $job);
					}else{
						$sender->sendMessage("§7[§6JobUI§7] " . $this->getMessage("nojob-retire-message"));
					}
				}else{
					$sender->sendMessage("§7[§6JobUI§7] " . "§cYou can't get retired in this world");
				}
			}
		}
		return true;
	}

	public function FormJob($player){
		$form = new SimpleForm(function (Player $player, int $data = null){
			if($data === null){
				return true;
				}
				switch($data){
					case "0";
					$this->FormJobJoin($player);
					break;
					
					case "1";
					$this->FormInfo($player);
					break;
					
					case "2";
					if($this->player->exists($player->getName())){
						$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("myjob-message") . $this->player->get($player->getName()));
					}else{
						$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("nojob-message"));
					}
					
					break;
					
					case "3";
					if($this->player->exists($player->getName())){
						$job = $this->player->get($player->getName());
						$this->player->remove($player->getName());
						$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("retire-message") . $job);
					}else{
						$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("nojob-retire-message"));
					}
					break;
				}
			});
			$form->setTitle($this->getMessage("title-mainui"));
			if($this->player->exists($player->getName())){
				$form->setContent($this->getMessage("myjob-text-mainui") . $this->player->get($player->getName()));
			}else{
				$form->setContent($this->getMessage("nojob-text-mainui"));
			}			
			$form->addButton($this->getMessage("jobjoin-button-mainui"));
			$form->addButton($this->getMessage("jobsinfo-button-mainui"));
			$form->addButton($this->getMessage("myjob-button-mainui"));
			$form->addButton($this->getMessage("retire-button-mainui"));
			$player->sendForm($form);
			return $form;
	}

	public function FormJobJoin($player){
		$form = new SimpleForm(function (Player $player, int $data = null){
			if($data === null){
				return true;
			}
			$i = 0;
			foreach($this->jobs->getAll() as $name => $job){
				switch($data){
					case "$i";
						if ($player->hasPermission($job["Permission"]) or $player->hasPermission("jobui.job.*") or $player->hasPermission("jobui.*")){
							$this->player->set($player->getName(), "$name");
							$job = $this->player->get($player->getName());
							$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("jobjoin-message") . $job);
							break;
						}else{
							$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("jobjoin-noperm-message") . $name);
						}
					}
				$i++;
				}
			}
		);
			
			$form->setTitle($this->getMessage("title-jobjoinui"));
			
		foreach($this->jobs->getAll() as $name => $job){
			$form->addButton($job["Button-Name"], $job["Image-Type"], $job["Image"]);
		}
			
			$player->sendForm($form);
			return $form;
	}

	public function FormInfo($player){
		$form = new SimpleForm(function (Player $player, $data = null){					
		if($data === null){
			return true;
		}
			switch($data){
				case 0:
				break;
			}
		});
		$form->setTitle($this->getMessage("title-myjobinfoui"));
		
		if($this->player->exists($player->getName())){
			$job = $this->jobs->get($this->player->get($player->getName()));
			$form->setContent($job["Info"]);
		}else{
			$form->setContent($this->getMessage("nojob-text-myjobinfoui"));
		}
		$form->addButton($this->getMessage("exit-button-myjobinfoui"));
		$player->sendForm($form);
	}
}
