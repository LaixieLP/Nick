
<?php

// Nick by AryToNeX

namespace Nick;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}

		if(!file_exists($this->getDataFolder()."nicks.yml")){
			yaml_emit_file($this->getDataFolder()."nicks.yml", []);
		}

		if(!file_exists($this->getDataFolder()."config.yml")){
			$this->saveDefaultConfig();
		}

		$this->nicks = new Config($this->getDataFolder()."nicks.yml", Config::YAML);
	}

	public function onPreLogin(PlayerPreLoginEvent $event){
		if($this->getConfig()->get("keep-nick")){
			if($this->nicks->exists(strtolower($event->getPlayer()->getName()))){
				$event->getPlayer()->setDisplayName($this->nicks->get(strtolower($event->getPlayer()->getName())));
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if(strtolower($command->getName()) == "nick"){

			// if no subcommand is setted
			if(!(isset($args[0]))){
				$sender->sendMessage($this->getConfig()->get("no-subcommand"));
				return true;
			}

			// if command sender is not a player
			if(!($sender instanceof Player)){
				$sender->sendMessage($this->getConfig()->get("no-run-in-console"));
				return true;
			}

			if(isset($args[0])){

				// Help
				if(strtolower($args[0]) == $this->getConfig()->get("help-command")){
					if($sender->hasPermission("nick.help")){
						$sender->sendMessage($this->getConfig()->get("help-header"));
						if($sender->hasPermission("nick.set")){
							$sender->sendMessage($this->getConfig()->get("help-command-colour")."/nick ".$this->getConfig()->get("set-command")." ".$this->getConfig()->get("help-usage-colour").$this->getConfig()->get("set-usage").$this->getConfig()->get("help-two-points-colour").": ".$this->getConfig()->get("help-description-colour").$this->getConfig()->get("set-description"));
						}
						if($sender->hasPermission("nick.admin.set")){
							$sender->sendMessage($this->getConfig()->get("help-command-colour")."/nick ".$this->getConfig()->get("set-command")." ".$this->getConfig()->get("help-usage-colour").$this->getConfig()->get("set-admin-usage").$this->getConfig()->get("help-two-points-colour").": ".$this->getConfig()->get("help-description-colour").$this->getConfig()->get("set-admin-description"));
						}
						if($sender->hasPermission("nick.reset")){
							$sender->sendMessage($this->getConfig()->get("help-command-colour")."/nick ".$this->getConfig()->get("reset-command").$this->getConfig()->get("help-two-points-colour").": ".$this->getConfig()->get("help-description-colour").$this->getConfig()->get("reset-description"));
						}
						if($sender->hasPermission("nick.admin.reset")){
							$sender->sendMessage($this->getConfig()->get("help-command-colour")."/nick ".$this->getConfig()->get("reset-command")." ".$this->getConfig()->get("help-usage-colour").$this->getConfig()->get("reset-admin-usage").$this->getConfig()->get("help-two-points-colour").": ".$this->getConfig()->get("help-description-colour").$this->getConfig()->get("reset-admin-description"));
						}
						if($sender->hasPermission("nick.see")){
							$sender->sendMessage($this->getConfig()->get("help-command-colour")."/nick ".$this->getConfig()->get("see-command")." ".$this->getConfig()->get("help-usage-colour").$this->getConfig()->get("see-usage").$this->getConfig()->get("help-two-points-colour").": ".$this->getConfig()->get("help-description-colour").$this->getConfig()->get("see-description"));
						}
					}else{
						$sender->sendMessage($this->getConfig()->get("no-permission-help"));
					}
					return true;
				}

				// Set
				elseif(strtolower($args[0]) == $this->getConfig()->get("set-command")){

					// If nick is not specified
					if(!(isset($args[1]))){
						$sender->sendMessage($this->getConfig()->get("must-specify-nick"));
					}

					// Normal Player
					if(isset($args[1]) and !(isset($args[2]))){
						if($sender->hasPermission("nick.set")){
							$sender->setDisplayName($args[1]);
							$sender->sendMessage($this->getConfig()->get("set"));
							if($this->getConfig()->get("keep-nick")){
								$this->nicks->set(strtolower($sender->getName()), $args[1]);
								$this->nicks->save();
							}
						}else{
							$sender->sendMessage($this->getConfig()->get("no-permission-set"));
						}
					}

					// Admin player
					if(isset($args[1]) and isset($args[2])){
						if($sender->hasPermission("nick.admin.set")){
							foreach($this->getServer()->getOnlinePlayers() as $players){
								if(strtolower($players->getName()) == strtolower($args[2])){
									$players->setDisplayName($args[1]);
									$players->sendMessage($this->getConfig()->get("set-by-admin"));
									$sender->sendMessage($this->getConfig()->get("set"));
									if($this->getConfig()->get("keep-nick")){
										$this->nicks->set(strtolower($players->getName()), $args[1]);
										$this->nicks->save();
									}
								}else{
									return true;
								}
							}
						}else{
							$sender->sendMessage($this->getConfig()->get("no-permission-admin-set"));
						}
					}
					return true;
				}

				// Reset
				elseif(strtolower($args[0]) == $this->getConfig()->get("reset-command")){

					// Normal player
					if(!(isset($args[1]))){
						if($sender->hasPermission("nick.reset")){
							$sender->setDisplayName($sender->getName());
							$sender->sendMessage($this->getConfig()->get("reset"));
							if($this->nicks->exists(strtolower($sender->getName()))){
								$this->nicks->remove(strtolower($sender->getName()));
								$this->nicks->save();
							}
						}else{
							$sender->sendMessage($this->getConfig()->get("no-permission-reset"));
						}
					}

					// Admin player
					if(isset($args[1])){
						if($sender->hasPermission("nick.admin.reset")){
							foreach($this->getServer()->getOnlinePlayers() as $players){
								if((strtolower($players->getName()) or strtolower($players->getDisplayName())) == strtolower($args[1])){
									$players->setDisplayName($players->getName());
									$players->sendMessage($this->getConfig()->get("reset-by-admin"));
									$sender->sendMessage($this->getConfig()->get("reset-admin"));
									if($this->nicks->exists(strtolower($players->getName()))){
										$this->nicks->remove(strtolower($players->getName()));
										$this->nicks->save();
									}
								}else{
									return true;
								}
							}
						}else{
							$sender->sendMessage($this->getConfig()->get("no-permission-admin-reset"));
						}
					}
					return true;
				}

				// See
				elseif(strtolower($args[0]) == $this->getConfig()->get("see-command")){
					if($sender->hasPermission("nick.see")){
						if(isset($args[1])){
							foreach($this->getServer()->getOnlinePlayers() as $players){
								if((strtolower($players->getName()) or strtolower($players->getDisplayName())) == strtolower($args[1])){
									if($players->getName() !== $players->getDisplayName()){
										$sender->sendMessage(str_replace(["{player_nick}","{player_name}"], [$players->getDisplayName(), $players->getName()], $this->getConfig()->get("see")));
									}else{
										$sender->sendMessage($this->getConfig()->get("see-no-nick"));
									}
								}else{
									return true;
								}
							}
						}else{
							$sender->sendMessage($this->getConfig()->get("must-specify-player"));
						}
					}else{
						$sender->sendMessage($this->getConfig()->get("no-permission-see"));
					}
					return true;
				}

			}
		}
	}
}
