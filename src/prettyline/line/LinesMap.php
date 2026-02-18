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

namespace prettyline\line;

use InvalidArgumentException;
use SmartCommand\utils\SingletonTrait;

final class LinesMap 
{

    use SingletonTrait;

    /** @var array<int,Line> */
    protected $lines = [];

    public static function init() : self
    {
        $instance = new self();
        self::setInstance($instance);
        return $instance;
    }

    public function registerLine(Line $line)
    {
        $id = $line->getId();
        if (isset($this->lines[$id])) {
            throw new InvalidArgumentException("Line $id is already registered in lines map!");
        }
        $this->lines[$id] = $line;
    }

    public function unregisterLine(Line $line)
    {
        $id = $line->getId();
        unset($this->lines[$id]);
    }

    public function fetchLine(int $id) 
    {
        return $this->lines[$id] ?? null;
    }
}