<?php

declare(strict_types=1);

namespace Meru\JoinQuitSendActionbar;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

    /**
     * @var $Config
     */
    private $Config;

    public function onEnable() {
        parent::onEnable();
        $this->getLogger()->info($this->getDescription()->getFullName() . "を読み込みました。");
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->Config = new Config($this->getDataFolder() . "SendActionbarMessage.yml", Config::YAML , array(
            'join_message' => '§e%playername Joined the game',
            'quit_message' => '§e%playername Quited the game'
        ));
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $join_message = $this->Config->get('join_message');
        $playername = $player->getName();
        $this->allsendtip($join_message);
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (/** @noinspection PhpUnusedParameterInspection */ int $currentTick) use ($player) :void {
            $player->getLevel()->addParticle(new particle\HugeExplodeSeedParticle($player));
            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_EXPLODE);
            $player->sendMessage("めるはばかpart2");
        }),10);

    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $quit_message = $this->Config->get('quit_message');
        $playername = $player->getName();
        $this->allsendtip($quit_message);
        $this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function (/** @noinspection PhpUnusedParameterInspection */ int $currentTick) use ($player): void {
            new particle\HugeExplodeSeedParticle($player);
            new HugeExplodeParticle($player);
            new particle\ExplodeParticle($player);
        }), 10,20);
    }

    public function allsendtip(string $message){
        foreach (Server::getInstance()->getOnlinePlayers() as $player){
            $player->sendTip($message);
        }
    }

}
