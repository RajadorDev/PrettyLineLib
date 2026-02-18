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

use pocketmine\Player;

interface LineHolder 
{


    /**
     * @return string[]
     */
    public function getTextLines() : array;

    /**
     * @param Player $player
     * @param string $text
     * @param Line|null $line
     * @return string
     */
    public function getAditionalPlayerText(Player $player, string $text, Line $line = null) : string;

    /**
     * @param integer $index
     * @return Line|null
     */
    public function getLine(int $index);

    /**
     * @return Line[]
     */
    public function getLines() : array;

    /**
     * @param Player $player
     * @param integer $action
     * @param Line|null $fromLine
     * @return void
     */
    public function onTouch(Player $player, int $action, Line $line = null);

}