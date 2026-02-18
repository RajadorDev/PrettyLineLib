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

namespace prettyline\utils\promise;

use utils\promise\exception\PromiseResolverException;
use pocketmine\Server;

class PromiseResolver 
{

    /** @var boolean */
    protected $finished = false, $resolved = false;

    /** @var mixed */
    protected $result;

    /** @var Promise|null */
    protected $promise = null;

    public function getPromise() : Promise
    {
        if (is_null($this->promise)) {
            $this->promise = new Promise($this);
        }
        return $this->promise;
    }

    public function getResult()
    {
        if (!$this->resolved) {
            throw new PromiseResolverException('Promise was not resolved');
        }
        return $this->result;
    }

    public function isResolved() : bool 
    {
        return $this->resolved;
    }

    public function isErrorCatched() : bool 
    {
        return ($this->finished && !$this->resolved);
    }

    protected function checkFinished()
    {
        if ($this->finished)
        {
            throw new PromiseResolverException("Promise is already finished");
        }
    }

    public function resolve($value)
    {
        $this->checkFinished();
        $this->setFinished(true);
        $this->result = $value;
        $this->resolved = true;
        if ($this->promise) {
            $this->getPromise()->onResolve();
        }
        
    }

    public function error(string $errorMessage = null)
    {
        $this->checkFinished();
        $this->setFinished(true);
        $this->resolved = false;
        if ($this->promise) {
            $this->promise->onError();
        }

        if ($errorMessage) {
            Server::getInstance()->getLogger()->error($errorMessage);
        }
    }

    protected function setFinished(bool $set)
    {
        $this->finished = $set;
    }

}