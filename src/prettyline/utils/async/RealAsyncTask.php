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

namespace prettyline\utils\async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

/**
 * Pocketmine 2, does not save thread store values correctly. 
 * So i (Rajador) created this class to replace default pm2 async task save methods.
 */
abstract class RealAsyncTask extends AsyncTask
{

    public function saveToThreadStore($identifier, $value)
    {
        $identifier = $this->createThreadStoreId($identifier);
        return parent::saveToThreadStore($identifier, $value);
    }

    public function getFromThreadStore($identifier)
    {
        $identifier = $this->createThreadStoreId($identifier);
        return parent::getFromThreadStore($identifier);
    }

    protected function createThreadStoreId(string $name) : string 
    {
        return spl_object_hash($this) . ':' . $name;
    }

    public static function schedule(RealAsyncTask $task) : RealAsyncTask
    {
        Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
        return $task;
    }

}