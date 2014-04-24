<?php

namespace ChatFilter;

class ChatFilter extends \pocketmine\plugin\PluginBase implements \pocketmine\command\CommandExecutor, \pocketmine\event\Listener{
	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->config = (new \pocketmine\utils\Config($this->getDataFolder()."config.yml", \pocketmine\utils\Config::YAML, array(
			"messages" => array(
				"fuck"
			),
			"identify-capital-alphabet" => false,
			"mosaic" => "*"
		)))->getAll();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $command, $label, array $params){
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
	
	public function onChatEvent(\pocketmine\event\player\PlayerChatEvent $event){
		$message = $event->getMessage();
		foreach($this->config["messages"] as $m){
			if(($this->config["identify-capital-alphabet"] ? strpos($message, $m) : stripos($message, $m)) !== false){
				$cnt = strlen($m);
				$mosaic = str_repeat($this->config["mosaic"], $cnt);
				$message = str_ireplace($m, $mosaic, $message);
			}
		}
		$event->setMessage($message);
	}
	
	public function onDisable(){
		$config = (new \pocketmine\utils\Config($this->getDataFolder()."config.yml", \pocketmine\utils\Config::YAML));
		$config->setAll($this->config);
		$config->save();
	}
}