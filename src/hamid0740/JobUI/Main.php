<?php

 /*
 * The original EconomyJob is written by OneBone
 * Used to FormAPI Library virion by jojoe77777
 * added the UI and make it Advanced by hamid0740
 * Discord: hamid0740#3725
 * Github: https://github.com/hamid0740/
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

use jojoe77777\FormAPI\SimpleForm;

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener{

	/** @var Config */
	private $jobs;
	/** @var Config */
	private $player;
	/** @var Config */
	private $messages;
	/** @var Config */
	private $buttons;

	/** @var  EconomyAPI */
	private $api;

	/** @var Main */
	private static $instance;

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->saveResource("jobs.yml");
		$this->saveResource("messages.yml");
		$this->saveResource("buttons.yml");
		$this->jobs = new Config($this->getDataFolder() . "jobs.yml", Config::YAML);
		$this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
		$this->buttons = new Config($this->getDataFolder() . "buttons.yml", Config::YAML);
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
	* Get JobUI buttons
	*
	* @param string $id
	*
	* @return string | bool
	*/
	public function getButton($id){
		if($this->buttons->exists($id)){
			return $this->buttons->get($id);
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
			if(isset($job[$block->getID().":".$block->getDamage().":break"])){
				$money = $job[$block->getID().":".$block->getDamage().":break"];
				if ($player->hasPermission("jobui.earn.break")) {
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
			if(isset($job[$block->getID().":".$block->getDamage().":place"])){
				$money = $job[$block->getID().":".$block->getDamage().":place"];
				if ($player->hasPermission("jobui.earn.place")) {
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
				if ($sender->hasPermission("jobui.command.job")) {
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
				if ($sender->hasPermission("jobui.command.retire")){
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
					$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("myjob-message") . $this->player->get($player->getName()));
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
			$form->setContent($this->getMessage("myjob-text-mainui")  . $this->player->get($player->getName()));
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
						$this->player->set($player->getName(), "$name");
						$job = $this->player->get($player->getName());
						$player->sendMessage("§7[§6JobUI§7] " . $this->getMessage("jobjoin-message") . $job);
						break;
					}
				$i++;
				}
			}
		);
			
			$form->setTitle($this->getMessage("title-jobjoinui"));
			
		foreach($this->jobs->getAll() as $name => $job){
			if($this->buttons->exists("name-" . $name) and $this->buttons->exists("image-type-" . $name) and $this->buttons->exists("image-" . $name)){
				$form->addButton($this->getButton("name-" . $name), $this->getButton("image-type-" . $name), $this->getButton("image-" . $name));
			}else{
				$form->addButton($this->getMessage("color-jobsname-jobjoinui") . $name);
			}
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
		$form->setTitle($this->getMessage("title-jobsinfoui"));
		$form->setContent($this->getMessage("text-jobsinfoui"));
		$form->addButton($this->getMessage("exit-button-jobsinfoui"));
		$player->sendForm($form);
	}
}
