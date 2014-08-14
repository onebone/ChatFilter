<?php

namespace ChatFilter;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerChatEvent;

class ChatFilter extends PluginBase implements Listener{
	private $mosaicList;

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->config = (new Config($this->getDataFolder()."config.yml", Config::YAML, array(
			"messages" => array(
				"fuck"
			),
			"identify-capital-alphabet" => false,
			"mosaic" => "*"
		)))->getAll();
		
		$this->mosaicList = array();
		foreach($this->config["messages"] as $m){
			$this->mosaicList[] = str_repeat($this->config["mosaic"], strlen($m));
		}
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $params){
		switch($command->getName()){
			case "chatfilter":
			$output = "[ChatFilter] Filtered words : \n";
			foreach($this->config["messages"] as $m){
				$output .= $m.", ";
			}
			$output = substr($output, 0, -2);
			$sender->sendMessage($output);
			return true;
			case "filter":
			$sub = array_shift($params);
			if(trim($sub) === ""){
				usage:
				$sender->sendMessage("[ChatFilter] Usage: /filter <add | del | reload> [message]");
				return true;
			}
			switch($sub){
				case "add":
				case "+":
				$message = array_shift($params);
				if(trim($message) === ""){
					goto usage;
				}
				$this->config["messages"][] = $message;
				$sender->sendMessage("[ChatFilter] Added \"$message\" to the filter list");
				break;
				case "rm":
				case "del":
				case "-":
				$message = array_shift($params);
				if(trim($message) === ""){
					goto usage;
				}
				$key = array_search($message, $this->config["messages"]);
				if($key === false){
					$sender->sendMessage("[ChatFilter] \"$message\" is not in the message list");
				}else{
					unset($this->config["messages"][$key]);
					$sender->sendMessage( "[ChatFilter] Removed \"$message\" from the list");
				}
				break;
				default:
				goto usage;
			}
			return true;
		}
	}
	
	public function onChatEvent(PlayerChatEvent $event){
		$message = $event->getMessage();
		$event->setMessage($this->config["identify-capital-alphabet"] ? str_replace($this->config["messages"], $this->mosaicList, $message) : str_ireplace($this->config["messages"], $this->mosaicList, $message));
	}
	
	public function onDisable(){
		$config = (new Config($this->getDataFolder()."config.yml", Config::YAML));
		$config->setAll($this->config);
		$config->save();
	}
}