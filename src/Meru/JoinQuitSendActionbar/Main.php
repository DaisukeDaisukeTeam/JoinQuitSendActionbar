<?php

declare(strict_types=1);

namespace Meru\JoinQuitSendActionbar;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\particle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

    /**
     * @var Config $Config
     */
    private $Config;

    public function onEnable() {
        parent::onEnable();
        $this->getLogger()->info($this->getDescription()->getFullName() . "を読み込みました。");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->Config = new Config($this->getDataFolder() . "SendActionbarMessage.yml", Config::YAML, array(
            'join_message' => '§e%playername Joined the game',
            'quit_message' => '§e%playername Quited the game'
        ));
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $join_message = $this->Config->get('join_message');
        $playername = $player->getName();
        $this->allsendtip($playername, $join_message);
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(/** @noinspection PhpUnusedParameterInspection */ int $currentTick) use ($player): void{
            $player->getLevelNonNull()->addParticle(new particle\HugeExplodeSeedParticle($player));
            $player->getLevelNonNull()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_EXPLODE);
            //$player->sendMessage("めるはばかpart2");
        }), 10);

    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $level = $player->getLevelNonNull();
        $quit_message = $this->Config->get('quit_message');
        $playername = $player->getName();
        $this->allsendtip($playername, $quit_message);

        $pos = $player->asVector3();
        $this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function(/** @noinspection PhpUnusedParameterInspection */ int $currentTick) use ($player, $level, $pos): void{
            $level->addParticle(new particle\HugeExplodeSeedParticle($player));
            $level->addParticle(new HugeExplodeParticle($player));
            $level->addParticle(new particle\ExplodeParticle($player));
            foreach($level->getPlayers() as $target){
                if($pos->distanceSquared($target) <= 25){//5*5
                    $this->knockBack($target, $target->x - $pos->x, $target->z - $pos->z, 0.7);
                }

            }
        }), 10, 20);
    }

    /**
     * @param string $playername
     * @param string $message
     * @return void
     */
    public function allsendtip(string $playername, string $message){
        $this->getServer()->broadcastTip(str_replace("%playername", $playername, $message));
    }

    /**
     * @param Entity $target
     * @param float $x
     * @param float $z
     * @param float $base
     * @return void
     * @see \pocketmine\entity\Living::knockBack
     */
    public function knockBack(Entity $target, float $x, float $z, float $base = 0.4): void{
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }
        $f = 1 / $f;

        $motion = clone $target->getMotion();

        $motion->x /= 2;
        $motion->y /= 2;
        $motion->z /= 2;
        $motion->x += $x * $f * $base;
        $motion->y += $base;
        $motion->z += $z * $f * $base;

        if($motion->y > $base){
            $motion->y = $base;
        }

        $target->setMotion($motion);
    }
}
