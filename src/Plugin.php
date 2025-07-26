<?php declare(strict_types=1);

namespace Laraddon;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        // Optional: init things
    }

    public function deactivate(Composer $composer, IOInterface $io) {}
    public function uninstall(Composer $composer, IOInterface $io) {}

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstall',
            ScriptEvents::POST_UPDATE_CMD  => 'onPostInstall'
        ];
    }
    
    /**
     * Tun this after install or update packages complete
     *
     * @param  Event $event
     * @return void
     */
    public static function onPostInstall(Event $event): void
    {
        $io = $event->getIO();
        $composerFile = getcwd() . '/composer.json';

        $io->write("<info>🔧 Laraddon Plugin: Adding PSR-4 autoload for TestModule...</info>");

        $contents = file_get_contents($composerFile);
        if($contents === false) {
            $io->write("<error>❌ Failed to read composer.json file.</error>");
            return;
        }

        /** @var non-empty-array<string, array> $composerData */
        $composerData = json_decode($contents, true, 2, JSON_THROW_ON_ERROR);

        $autoload = &$composerData['autoload']['psr-4'];

        if (!isset($autoload['Addons\\'])) {
            $autoload['Addons\\'] = 'addons/';
            file_put_contents($composerFile, json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
            $io->write("<info>✅ PSR-4 namespace 'Addons\\' added.</info>");
    } else {
            $io->write("<comment>ℹ️ PSR-4 'Addons\\' already exists, skipping.</comment>");
        }

        $io->write("<info>🔃 Dumping autoload...</info>");
        shell_exec('composer dump-autoload');
    }
}
