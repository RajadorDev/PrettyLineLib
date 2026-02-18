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

use pocketmine\item\Item;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\Player;
use prettyline\utils\DefaultEntityDespawnPacketsTrait;
use pocketmine\utils\UUID;

trait HumanLineHolderTrait 
{

    use LineEntityHolderTrait, DefaultEntityDespawnPacketsTrait;

    /**
     * @return string
     */
    abstract public function getSkinId();

    /**
     * @return string
     */
    abstract public function getSkinData();

    /**
     * @return UUID
     */
    abstract public function getUniqueId();

    protected function sendSpawnPackets(Player $player)
    {

        $isInvisible = static::isDefaultInvisible();

        $uuid = $this->getUniqueId();

        if (!$isInvisible) {
            $packet = new PlayerListPacket();
            $packet->entries[] = [$uuid, $this->getId(), '', $this->getSkinId(), $this->getSkinData()];
            $packet->type = PlayerListPacket::TYPE_ADD;
            $player->dataPacket($packet);
        }

        $packet = new AddPlayerPacket();
        $packet->eid = $this->getId();
        $packet->uuid = $this->getUniqueId();
        $packet->username = '';
        $packet->x = $this->x;
        $packet->y = $this->y;
        $packet->z = $this->z;
        $packet->speedX = $packet->speedY = $packet->speedZ = 0;
        $packet->yaw = $this->yaw;
        $packet->pitch = $this->pitch;
        $packet->item = Item::get(Item::AIR);
        $packet->metadata = $this->generatePlayerMetadata($player);
        $player->dataPacket($packet);

        if (!$isInvisible) {
            $packet = new PlayerListPacket();
            $packet->type = PlayerListPacket::TYPE_REMOVE;
            $packet->entries[] = [$uuid];
            $player->dataPacket($packet);
        }
    }

}