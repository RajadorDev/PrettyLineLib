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

namespace prettyline\utils;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class ClosureTask extends Task
{

    /** @var callable `() : void` */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function onRun($currentTick)
    {
        ($this->callback)();
    }

    /**
     * @param integer $ticks
     * @param callable $callback `() : void`
     * @return ClosureTask
     */
    public static function scheduleDelayed(int $ticks, callable $callback) : ClosureTask
    {
        Server::getInstance()->getScheduler()->scheduleDelayedTask($task = new self($callback), $ticks);
        return $task;
    }

}