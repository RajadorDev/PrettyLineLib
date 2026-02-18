<?php

declare (strict_types=1);
 
/***
 *   
 * Rajador Developer
 * 
 * ▒█▀▀█ ░█▀▀█ ░░░▒█ ░█▀▀█ ▒█▀▀▄ ▒█▀▀▀█ ▒█▀▀█ 
 * ▒█▄▄▀ ▒█▄▄█ ░▄░▒█ ▒█▄▄█ ▒█░▒█ ▒█░░▒█ ▒█▄▄▀ 
 * ▒█░▒█ ▒█░▒█ ▒█▄▄█ ▒█░▒█ ▒█▄▄▀ ▒█▄▄▄█ ▒█░▒█
 * 
 * GitHub: https://github.com/rajadordev
 * 
 * Discord: rajadortv
 * 
 * 
**/ 

namespace prettyline\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use prettyline\line\LinesMap;
use pocketmine\network\protocol\InteractPacket;
use prettyline\line\LineHolder;
use prettyline\PrettyLinePlugin;
use Throwable;

final class LinesTouchListener implements Listener
{

    /** @var LinesMap */
    protected $lines;

    public static function init(PrettyLinePlugin $plugin)
    {
        $plugin->registerListener(new self());
    }

    public function __construct()
    {
        $this->lines = LinesMap::getInstance();
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled TRUE
     */
    public function lineInteraction(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        if ($packet instanceof InteractPacket) {
            $entityRuntimeId = (int) $packet->target;
            $target = $player->getLevel()->getEntity($entityRuntimeId);
            try {
                if ($target instanceof LineHolder) {
                    $event->setCancelled(true);
                    $target->onTouch($player, $packet->action);
                } else if ($line = $this->lines->fetchLine($entityRuntimeId)) {
                    $event->setCancelled(true);
                    $line->getHolder()->onTouch($player, $packet->action, $line);
                }
            } catch (Throwable $error) {
                PrettyLinePlugin::getInstance()->getLogger()->error((string) $error);
            }
        }
    }
}