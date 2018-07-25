<?php

/**
 * Copyright 2018 Swifty
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace skywars\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use skywars\arena\Arena;
use skywars\SkyWars;

/**
 * Class SkyWarsCommand
 * @package skywars\commands
 */
class SkyWarsCommand extends Command implements PluginIdentifiableCommand {

    /** @var SkyWars $plugin */
    protected $plugin;

    /**
     * SkyWarsCommand constructor.
     * @param SkyWars $plugin
     */
    public function __construct(SkyWars $plugin) {
        $this->plugin = $plugin;
        parent::__construct("skywars", "SkyWars Commands", \null, ["sw"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission("sw.cmd")) {
            $sender->sendMessage("You have not permissions to use this command!");
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage("Usage: /sw help");
            return;
        }

        switch ($args[0]) {
            case "help":
                if(!$sender->hasPermission("sw.cmd.help")) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                $sender->sendMessage"SkyWars Commands:\n" .
                    "/sw help : Displays List Of SkyWars Commands\n".
                    "/sw create : Create SkyWars Arena\n".
                    "/sw remove : Remove SkyWars Arena\n".
                    "/sw set : Set SkyWars Arena\n".
                    "/sw arenas : Displays List Of Arenas");

                break;
            case "create":
                if(!$sender->hasPermission("sw.cmd.create")) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /sw create <arenaName>");
                    break;
                }
                if(isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("> Arena $args[1] already exists!");
                    break;
                }
                $this->plugin->arenas[$args[1]] = new Arena($this->plugin, []);
                $sender->sendMessage("> Arena $args[1] created!");
                break;
            case "remove":
                if(!$sender->hasPermission("sw.cmd.remove")) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /sw remove <arenaName>");
                    break;
                }
                if(!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("> Arena $args[1] was not found!");
                    break;
                }

                /** @var Arena $arena */
                $arena = $this->plugin->arenas[$args[1]];

                foreach ($arena->players as $player) {
                    $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                }

                if(is_file($file = $this->plugin->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                unset($this->plugin->arenas[$args[1]]);

                $sender->sendMessage("> Arena removed!");
                break;
            case "set":
                if(!$sender->hasPermission("sw.cmd.set")) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                if(!$sender instanceof Player) {
                    $sender->sendMessage("> This command can be used only in-game!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("Usage: /sw set <arenaName>");
                    break;
                }
                if(isset($this->plugin->setters[$sender->getName()])) {
                    $sender->sendMessage("> You are already in setup mode!");
                    break;
                }
                if(!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("> Arena $args[1] does not found!");
                    break;
                }
                $sender->sendMessage("> You are joined setup mode.\n".
                    "- use help to display available commands\n"  .
                    "- or done to leave setup mode");
                $this->plugin->setters[$sender->getName()] = $this->plugin->arenas[$args[1]];
                break;
            case "arenas":
                if(!$sender->hasPermission("sw.cmd.arenas")) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                if(count($this->plugin->arenas) === 0) {
                    $sender->sendMessage("> There are 0 arenas.");
                    break;
                }
                $list = "> Arenas:\n";
                foreach ($this->plugin->arenas as $name => $arena) {
                    if($arena->setup) {
                        $list .= "- $name : disabled\n";
                    }
                    else {
                        $list .= "- $name : enabled\n";
                    }
                }
                $sender->sendMessage($list);
                break;
        }

    }

    /**
     * @return SkyWars|Plugin $skywars
     */
    public function getPlugin(): Plugin {
        return $this->plugin;
    }

}
