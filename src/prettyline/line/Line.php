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

use pocketmine\entity\Entity;
use pocketmine\entity\FallingSand;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class Line
{

    /** @var array<int,Player> */
    protected $viewers = [];

    /** @var LineHolder */
    protected $holder;

    /** @var int */
    protected $runtimeId;

    /** @var UUID */
    protected $uuid;

    /** @var Location */
    protected $position;

    /** @var string */
    protected $text;

    public function __construct(LineHolder $holder, Location $position, string $text)
    {
        $this->holder = $holder;
        $this->runtimeId = Entity::$entityCount++;
        $this->uuid = UUID::fromData((string) $this->runtimeId);
        $this->position = $position;
        $this->text = $text;
    }

    public function getLocation() : Location
    {
        return $this->position;
    }

    public function getUniqueId() : UUID
    {
        return $this->uuid;
    }

    public function getId() : int 
    {
        return $this->runtimeId;
    }

    public function getHolder() : LineHolder
    {
        return $this->holder;
    }

    /**
     * @return Player[]
     */
    public function getViewers() : array 
    {
        return $this->viewers;
    }

    public function getText() : string 
    {
        return $this->text;
    }

    public function setText(string $newText, bool $updateToAll = true) : Line
    {
        $oldText = $this->text;
        $this->text = $newText;
        if ($updateToAll && $newText !== $oldText) {
            if ($newText == '') {
                $this->respawnToAll();
            } else {
                $this->sendNewText();
            }
        }
        return $this;
    }

    protected function sendNewText()
    {
        foreach ($this->viewers as $player) {
            $data = [Player::DATA_NAMETAG => [Player::DATA_TYPE_STRING, $this->holder->getAditionalPlayerText($player, $this->text, $this)]];
            $this->sendMetadata($player, $data);
        }
    }

    protected function getMetadata(string $nametag) : array 
    {
        $playerFlags = 0;
        $flags = 0;
        $metadata = [
            Player::DATA_SHOW_NAMETAG => [Player::DATA_TYPE_BYTE, (int) ($nametag != '')],
            Player::DATA_NAMETAG => [Player::DATA_TYPE_STRING, $nametag],
            Player::DATA_NO_AI => [Player::DATA_TYPE_BYTE, 1],
            Player::DATA_SILENT => [Player::DATA_TYPE_BYTE, 1],
            Player::DATA_LEAD_HOLDER => [Player::DATA_TYPE_LONG, -1],
		    Player::DATA_LEAD => [Player::DATA_TYPE_BYTE, 0],
            Player::DATA_AIR => [Player::DATA_TYPE_SHORT, 300],
            FallingSand::DATA_BLOCK_INFO => [Player::DATA_TYPE_INT, 0 | (0 << 8)],
            Player::DATA_PLAYER_BED_POSITION => [Player::DATA_TYPE_POS, [0, 0, 0]]
        ];
        
        self::updateFlag(Player::DATA_FLAG_INVISIBLE, $flags);

        self::updateFlag(Player::DATA_PLAYER_FLAG_SLEEP, $playerFlags, false);
        self::updateFlag(Player::DATA_PLAYER_FLAG_DEAD, $playerFlags, false);

        $metadata[Player::DATA_PLAYER_FLAGS] = [Player::DATA_TYPE_BYTE, $playerFlags];
        $metadata[Player::DATA_FLAGS] = [Player::DATA_TYPE_BYTE, $flags];
        return $metadata;
    }

    /**
     * @param Player $player
     * @param array $metadata
     * @return void
     */
    protected function playerMetadataParser(Player $player, array &$metadata)
    {
        $nametag = $metadata[Player::DATA_NAMETAG][1];
        $customNametag = $this->holder->getAditionalPlayerText($player, $nametag, $this);
        $metadata[Player::DATA_NAMETAG][1] = $customNametag;
    }

    protected function generatePlayerMetadata(Player $player) : array 
    {
        $metadata = $this->getMetadata($this->getText());
        $this->playerMetadataParser($player, $metadata);
        return $metadata;
    }

    public static function updateFlag(int $propertyId, int &$currentFlag, bool $value = true)
    {
        $currentValue = ($currentFlag & (1 << $propertyId)) > 0;
        if ($value !== $currentValue) {
            $currentFlag ^= 1 << $propertyId;
        }
    }

    public function respawnToAll()
    {
        foreach ($this->viewers as $player) {
            $this->despawnFrom($player);
            $this->spawnTo($player);
        }
    }

    public function resendDataToAll()
    {
        foreach ($this->viewers as $player) {
            $this->sendMetadata($player);
        }
    }

    public function spawnTo(Player $player) : bool 
    {
        $playerId = $player->getLoaderId();
        if (!isset($this->viewers[$playerId])) {
            $this->sendSpawnPackets($player, $this->text);
            $this->viewers[$playerId] = $player;
            return true;
        }
        return false;
    }

    protected function sendSpawnPackets(Player $player) 
    {
        $packet = new AddPlayerPacket;
        $packet->eid = $this->getId();
        $packet->uuid = $this->uuid;
        $packet->username = '';
        $pos = $this->getLocation();
        $packet->x = $pos->x;
        $packet->y = $pos->y;
        $packet->z = $pos->z;
        $packet->speedX = $packet->speedY = $packet->speedZ = 0;
        $packet->yaw = $pos->yaw;
        $packet->pitch = $pos->pitch;
        $packet->item = Item::get(Item::AIR);
        $packet->metadata = $this->generatePlayerMetadata($player);
        $player->dataPacket($packet);
    }

    public function despawnFrom(Player $player) : bool 
    {
        $playerId = $player->getLoaderId();
        if (isset($this->viewers[$playerId])) {
            $this->sendDespawnPackets($player);
            unset($this->viewers[$playerId]);
            return true;
        }
        return false;
    }

    public function despawnFromAll()
    {
        foreach ($this->viewers as $viewer) {
            $this->despawnFrom($viewer);
        }
    }

    protected function sendDespawnPackets(Player $player)
    {
        $packet = new RemoveEntityPacket();
        $packet->eid = $this->getId();
        $player->dataPacket($packet);
    }

    public function sendMetadata(Player $player, array $metadata = null)
    {
        $packet = new SetEntityDataPacket();
        $packet->eid = $this->getId();
        $packet->metadata = $metadata ?? $this->generatePlayerMetadata($player);
        $player->dataPacket($packet);
    }

}