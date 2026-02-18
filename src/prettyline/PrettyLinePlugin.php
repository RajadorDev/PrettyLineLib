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

namespace prettyline;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use prettyline\line\LinesMap;
use prettyline\listener\LinesTouchListener;
use rajadordev\autoupdater\api\CheckUpdateScheduler;
use rajadordev\autoupdater\api\plugin\defaults\github\GitHubPluginUpdaterAPI;
use rajadordev\autoupdater\api\PluginUpdaterChecker;
use SmartCommand\utils\SingletonTrait;

class PrettyLinePlugin extends PluginBase
{
 
    use SingletonTrait;
 
    public function onLoad()
    {
        self::setInstance($this);
    }

    public function onEnable()
    {
        LinesMap::init();
        LinesTouchListener::init($this);

        CheckUpdateScheduler::getInstance()->schedule(
            PluginUpdaterChecker::create(
                $this,
                GitHubPluginUpdaterAPI::createFromPlugin(
                    $this,
                    'RajadorDev',
                    'PrettyLineLib'
                )
            )
        );
    }

    /**
     * @param string $identifier
     * @param mixed $defaultValue
     * @param boolean $warnConsole
     * @return mixed
     */
    public function getConfigValue(string $identifier, $defaultValue = null, bool $warnConsole = true)
    {
        $settings = $this->getConfig();
        if ($settings->exists($identifier)) {
            return $settings->get($identifier);
        } else if ($warnConsole) {
            $this->getLogger()->warning("Setting with id $identifier does not found!");
        }
        return $defaultValue;
    }

    public function registerListener(Listener $listener)
    {
        Server::getInstance()->getPluginManager()->registerEvents($listener, $this);
    }

}