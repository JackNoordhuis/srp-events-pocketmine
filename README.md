Single Responsibility Events: Virion
===============
_Separate your event listeners into separate classes with a single purpose._

### About

This virion is designed to help you separate your plugins event listeners into classes with a single purpose. Why bloat
a single class, or even a single listener method with a bunch of unrelated code that makes your plugin a giant mess? Give
each class a single job, make sure each method only performs one clearly defined task. Each event handler you create has
code driven configuration for setting up methods as listeners, this allows you to explicitly register listeners with the
options you need without having to rely on PocketMine magically doing it for you.

## Installation

The easiest way to use this virion in your plugins is to use the [poggit ci](https://poggit.pmmp.io) and it'll automatically
inject the library into the development phar on every commit. You should use [devirion](https://github.com/poggit/devirion)
for local testing and not rely on poggit to make the phar just for your testing.

## Usage

You must create an instance of `jacknoordhuis\srp\events\EventManager`:

```php
use jacknoordhuis\srp\events\EventManager;

class MyPlugin extends \pocketmine\plugin\PluginBase {
    private $eventManager = null;
    
    public function onEnable() {
        $this->eventManager = new EventManager($this);
    }
}
```

Then you can register all your event handlers:
```php
public function onEnable() {
    $this->eventManager = new EventManager($this);
    $this->eventManager->registerAll([
        new SeeIfNewPlayerHandler, //this handler would check if the player has played before
        new ChatFilterHandler, //this handler would check chat messages for 'bad' words
    ]);
}
```

And these handlers might look like:
```php
use jacknoordhuis\srp\events\HandlerList;

class SeeIfNewPlayerHandler extends \jacknoordhuis\srp\events\EventHandler {
    public function handles(HandlerList $list) : void {
        $list->handler('playerJoin')
            ->event(\pocketmine\event\player\PlayerJoinEvent::class);
    }
    
    public function playerJoin(PlayerJoinEvent $e) : void {
        if(($p = $e->getPlayer())->hasPlayedBefore()) {
            $p->sendMessage("Welcome back to this server!");
            return;
        }

        $p->sendMessage('Welcome to this server, here is some food to help you get started.');
        $p->getInventory()->addItem(\pocketmine\item\Item::get(\pocketmine\item\Item::STEAK, 0 , 16));
    }
}
```

And:
```php
use jacknoordhuis\srp\events\HandlerList;

class SeeIfNewPlayerHandler extends \jacknoordhuis\srp\events\EventHandler {
    public function handles(HandlerList $list) : void {
        $list->handler('playerChat')
            ->event(\pocketmine\event\player\PlayerChatEvent::class)
            ->priority(\pocketmine\event\EventPriority::HIGHEST);
    }
    
    public function playerChat(PlayerChatEvent $e) : void {
        if($this->containsProfane($e->getMessage())) {
            $e->setCancelled();
            $e->getPlayer()->sendMessage("You can't say that!");
        }
    }
    
    public function containsProfane(string $message) : bool {
        //check if the message contains a 'bad' word
    }
}
```

For your continence the `jacknoordhuis\srp\events\EventHandler` class has a method to retrieve the plugin which it was
registered from:
```php
use jacknoordhuis\srp\events\HandlerList;

class SeeIfNewPlayerHandler extends \jacknoordhuis\srp\events\EventHandler {
    public function handles(HandlerList $list) : void {
        //setup handler
    }
    
    public function playerChat(pocketmine\event\entity\EntityDamageEvent $e) : void {
        $this->getPlugin()->getLogger()->debug("damage event");
    }
}
```

You may find it helpful to add a PHPDoc block to the top of your handler classes so your IDE knows that `getPlugin` will
return an instance of your plugin:
```php
use jacknoordhuis\srp\events\HandlerList;

/**
 * @method pocketmine\plugin\Plugin|\yourname\yourplugin\PluginMain getPlugin()
 */
class MyEventHandler extends \jacknoordhuis\srp\events\EventHandler {
    //...
}
```

### Issues

Found a problem with SRP Events? Make sure to open an issue on the [issue tracker](https://github.com/JackNoordhuis/srp-events-pocketmine/issues)
and we'll get it sorted!

__The content of this repo is licensed under the GNU Lesser General Public License v3. A full copy of the license is available [here](LICENSE).__
