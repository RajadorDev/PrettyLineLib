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
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;

trait LineEntityHolderTrait 
{

    /** @var array<int,Line> */
    protected $lines = [];

    /**
     * @return string[]
     */
    abstract public function getTextLines() : array;

    /**
     * @param Player $player
     * @return void
     */
    abstract protected function sendSpawnPackets(Player $player);

    /**
     * @param Player $player
     * @return void
     */
    abstract protected function sendDespawnPackets(Player $player);

    /**
     * @return boolean
     */
    abstract public static function isDefaultInvisible() : bool;

    protected function initEntity() 
    {
        parent::initEntity();
        $this->updateAllText();
        if (static::isDefaultInvisible()) {
            $this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
        }
    }

    public function getLine(int $index)
    {
        return $this->lines[$index] ?? null;
    }

    public function getLines() : array 
    {
        return $this->lines;
    }

    public function removeAllLines()
    {
        foreach ($this->lines as $position => $line) {
            $this->removeLine($position);
        }
    }

    public function removeLine(int $index) : bool 
    {
        if ($line = $this->getLine($index)) {
            $line->despawnFromAll();
            unset($this->lines[$index]);
            LinesMap::getInstance()->unregisterLine($line);
            return true;
        }
        return false;
    }

    public function registerLine(int $index, Line $line, bool $spawnToAllViewers = true)
    {
        if (isset($this->lines[$index])) {
            throw new InvalidArgumentException("There is already a line registered in $index index");
        }
        $this->lines[$index] = $line;
        LinesMap::getInstance()->registerLine($line);
        if ($spawnToAllViewers) {
            foreach ($this->getViewers() as $player) {
                $line->spawnTo($player);
            }
        }
    }

    public static function getLineHeight() : float 
    {
        return 0.24;
    }

    public function updateAllText()
    {
        $texts = $this->getTextLines();
        $textCount = count($texts);
        if ($textCount > 1) {
            $lineNumber = $textCount;

            foreach ($this->lines as $lineIndex => $lineAlreadyRegistered) {
                if ($lineIndex > $lineNumber) {
                    $this->removeLine($lineIndex);
                }
            }

            foreach ($texts as $textLine) {
                $index = $lineNumber--;
                if ($index <= 1) {
                    break;
                }
                if ($line = $this->getLine($index)) {
                    $line->setText($textLine, true);
                    continue;
                }
                $this->registerLine($index, new Line($this, $this->createLineLocation($index), $textLine));
            }

            $lastText = array_pop($texts);
            $this->setNameTag($lastText);
            $this->setNameTagVisible(true);
        } else {
            $this->removeAllLines();
            $uniqueLine = array_shift($texts);
            if ($uniqueLine) {
                $this->setNameTag($uniqueLine);
                $this->setNameTagVisible(true);
            } else {
                $this->setNameTag('');
                $this->setNameTagVisible(false);
            }
        }
    }

    public function spawnTo(Player $player) 
    {
        $playerId = $player->getLoaderId();
        if (!isset($this->hasSpawned[$playerId])) {
            $this->spawnLinesTo($player);
            $this->sendSpawnPackets($player);
            $this->hasSpawned[$playerId] = $player;
            return true;
        }
        return false;
    }

    public function despawnFrom(Player $player)
    {
        $playerId = $player->getLoaderId();
        if (isset($this->hasSpawned[$playerId])) {
            $this->despawnLinesTo($player);
            $this->sendDespawnPackets($player);
            unset($this->hasSpawned[$playerId]);
        }
    }

    protected function spawnLinesTo(Player $player)
    {
        foreach ($this->lines as $line) {
            $line->spawnTo($player);
        }
    }

    protected function despawnLinesTo(Player $player)
    {
        foreach ($this->lines as $line) {
            $line->despawnFrom($player);
        }
    }

    protected function createLineLocation(int $lineIndex) : Location
    {
        return Location::fromObject($this->makeLinePosition($lineIndex), $this->level, $this->yaw, $this->pitch);
    }

    protected function makeLinePosition(int $lineNumber) : Vector3
    {
        $lineNumber -= 1;
        return new Vector3($this->x, $this->y + ($lineNumber * static::getLineHeight()), $this->z);
    }

    public function generatePlayerMetadata(Player $player) : array 
    {
        $properties = $this->dataProperties;
        $properties[Player::DATA_NAMETAG][1] = $this->getAditionalPlayerText($player, $this->getNameTag());
        return $properties;
    }

    public function getAditionalPlayerText(Player $player, string $text, Line $line = null) : string {
        return $text;
    }

    /**
     * @param Player|Player[] $players
     * @param array|null $data
     * @return void
     */
    public function sendData($players, array $data = null) 
    {
        if ($players instanceof Player) {
            $players = [$players];
        }

        foreach ($players as $player) {
            if (is_null($data)) {
                parent::sendData($player, $this->generatePlayerMetadata($player));
            } else if (isset($data[Player::DATA_NAMETAG])) {
                $nametag = $this->getAditionalPlayerText($player, $data[Player::DATA_NAMETAG][1]);
                $data[Player::DATA_NAMETAG][1] = $nametag;
                parent::sendData($player, $data);
            } else {
                parent::sendData($player, $data);
            }
        }
    }

    /**
     * @param float|int $damage
     * @param EntityDamageEvent $event
     * @return void
     */
    public function attack($damage, EntityDamageEvent $event) {}

}