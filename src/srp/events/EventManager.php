<?php

/**
 * EventManager.php – pm-srp-events
 *
 * Copyright (C) 2019 Jack Noordhuis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Jack
 *
 */

declare(strict_types=1);

namespace jacknoordhuis\srp\events;

use pocketmine\plugin\MethodEventExecutor;
use pocketmine\plugin\Plugin;

/**
 * Manages the registration of event handlers for a plugin.
 */
class EventManager {

	/** @var \pocketmine\plugin\Plugin */
	private $plugin;

	/** @var \jacknoordhuis\srp\events\EventHandler[] */
	private $eventHandlers = [];

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin() : Plugin {
		return $this->plugin;
	}

	/**
	 * Register an event handler to the pocketmine event manager.
	 *
	 * @param EventHandler $handler
	 *
	 * @throws \ReflectionException
	 */
	public function register(EventHandler $handler) : void {
		$handler->handles($list = new HandlerList($handler));
		foreach($list->handlers() as $method) {
			$this->plugin->getServer()->getPluginManager()->registerEvent($method->fetchEvent(), $handler, $method->fetchPriority(), new MethodEventExecutor($method->fetchMethod()), $this->plugin, $method->fetchIgnoreCancelled());
			$this->plugin->getLogger()->debug("Registered listener for " . (new \ReflectionClass($method->fetchEvent()))->getShortName() . " for " . (new \ReflectionObject($handler))->getShortName() . "::" . $method->fetchMethod() . "()");
		}

		$this->eventHandlers[] = $handler;
		$handler->setManager($this);
	}

	/**
	 * Register an array of event handlers.
	 *
	 * @param \jacknoordhuis\srp\events\EventHandler[] $handlers
	 *
	 * @throws \ReflectionException
	 */
	public function registerAll(array $handlers) : void {
		foreach($handlers as $handler) {
			$this->register($handler);
		}
	}

}
