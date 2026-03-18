# PrettyLineLib 🎨

PrettyLineLib is a library created to build entities with separated lines in MCPE 0.14/15 (PocketMine 2). It fixes the issue of disorganized lines in those versions, making the nametag text look clean and well-formatted as shown in the image.

![Demo](/assets/demo.png)

## Comunity 💬

**Discord:**

<a href="https://discord.gg/HkfMbBN2AD"><img src="https://img.shields.io/discord/982037265075302551?label=discord&color=7289DA&logo=discord" alt="Discord"></a>

## Optimization 🔥

This library is optimized and does not create more entities than necessary for the lines. Instead, it creates fake entities that are not updated by world ticks and are not saved in the world binary data either.

## Requirements 📰

* PocketMine 2: `This library is only available for PocketMine 2.0.0 and will not receive updates for the latest PocketMine version, since the issue it fixes only exists in older Minecraft PE versions`

* SmartCommand: `This library depends on some utilities from SmartCommand and may use its command classes in the future`
  - `Download`: https://github.com/RajadorDev/SmartCommand/tree/pm-2.0.0

* AutoPluginUpdater: `This plugin will update PrettyLineLib automatically when has some new version`
  - `Download:`: https://github.com/RajadorDev/AutoPluginUpdater

## Documentation 📚

Fist, you need to add PrettyLineLib as dependence of your plugin in `plugin.yml` file:

```yml
depend: [PrettyLineLib]
```

To create an entity, you must first extend the `pocketmine\entity\Entity` class. After that, implement the `prettyline\line\LineHolder` interface so the touch system can recognize when a player interacts with it.

To make things easier, you can use the following traits:

* `prettyline\line\HumanLineHolderTrait`: `To create a human entity`
* `prettyline\line\LineEntityHolderTrait`: `To create a generic entity`

### Example using `prettyline\line\LineEntityHolderTrait`:

```php
<?php

use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\InteractPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\Player;
use prettyline\line\Line;
use prettyline\line\LineEntityHolderTrait;
use prettyline\line\LineHolder;

class MyEntity extends Entity implements LineHolder
{

    /** If not used, you will need to implement the LineHolder interface methods yourself */
    use LineEntityHolderTrait;

    /**
     * Define whether your entity should be invisible by default
     *
     * @return boolean
     */
    public static function isDefaultInvisible(): bool
    {
        return false;
    }

    /**
     * Each string in the array represents a new line
     *
     * @return string[]
     */
    public function getTextLines(): array
    {
        return [
            '§l§bPRETTY LINE LIB',
            '§7Line one',
            '§7Line two :)'
        ];
    }

    /**
     * You do not need to check if it has already been spawned for the player here.
     * The LineHolderTrait will handle that.
     *
     * @param Player $player
     * @return void
     */
    protected function sendSpawnPackets(Player $player)
    {
        $pk = new AddEntityPacket();
        // ...Send your entity spawn packets here
    }

    /**
     * Called to remove the entity for the player
     *
     * @param Player $player
     * @return void
     */
    protected function sendDespawnPackets(Player $player)
    {
        $pk = new RemoveEntityPacket();
        // ...Send the packets to remove the entity here
    }

    /**
     * Called when the player interacts with the entity (both left and right click)
     * @see InteractPacket
     * 
     * @param Player $player
     * @param integer $action
     * @param Line|null $line
     * @return void
     */
    public function onTouch(Player $player, int $action, Line $line = null)
    {
        // ...Do something or ignore
    }

}
```
