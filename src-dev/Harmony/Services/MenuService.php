<?php
/*
 * This file is part of the Harmony core package.
 *
 * (c) 2012-2015 David Negrier <david@mouf-php.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
namespace Harmony\Services;
use Harmony\HarmonyException;
use Interop\Container\ContainerInterface;
use Mouf\Html\Widgets\Menu\Menu;
use Mouf\Html\Widgets\Menu\MenuItem;
use Mouf\Html\Widgets\Menu\MenuItemInterface;
use Mouf\Menu\ChooseInstanceMenuItem;

/**
 * This class contains utility functions to edit the Harmony menu.
 * 
 * @author David Negrier
 */
class MenuService {

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @return MenuItemInterface
	 */
	public function getMainMenu() {
		return $this->container->get('mainMenu');
	}

	/**
	 * Registers a new menuItem instance to be displayed in Harmony.
	 * Note: this function takes care of duplicates. The menu will not be registered if another menu item has the same label
	 * and same url.
	 *
	 * @param string $label The label of the menu
	 * @param string $url The URL of the link
	 * @param MenuItem|Menu $parentMenuItem The parent menu item
	 * @param float $priority The position of the menu
	 * @return MenuItemInterface
	 */
	public function registerMenuItem($label, $url, $parentMenuItem, $priority = 50.0) {
		foreach ($parentMenuItem->getChildren() as $child) {
			/* @var $child MenuItemInterface */
			if ($child->getLabel() == $label && $child->getUrl() == $url) {
				return $child;
			}
		}

		$menuItem = new MenuItem($label, $url);
		$menuItem->setPriority($priority);

		if ($parentMenuItem instanceof Menu) {
			$parentMenuItem->addChild($menuItem);
		} elseif ($parentMenuItem instanceof MenuItem) {
			$parentMenuItem->addMenuItem($menuItem);
		} else {
			throw new HarmonyException('$parentMenuItem must be an instance of Menu or MenuItem.');
		}
		return $menuItem;
	}

	/**
	 * Registers a new menuItem instance to be displayed in Harmony main menu that triggers
	 * a popup to choose an instance from type $type.
	 * The name of the instance will automatically be added to the URL: [URL]?name=[instance_name]
	 *
	 * @param string $label The label of the menu
	 * @param string $url The URL of the link
	 * @param string $type A fully qualified class name or interface that instances should implement
	 * @param MenuItem|Menu $parentMenuItem The parent menu item
	 * @param float $priority The position of the menu
	 * @return ChooseInstanceMenuItem
	 */
	public function registerChooseInstanceMenuItem($label, $url, $type, $parentMenuItem, $priority = 50.0) {

		$menuItem = new ChooseInstanceMenuItem($label, $url, $type);
		$menuItem->setPriority($priority);

		if ($parentMenuItem instanceof Menu) {
			$parentMenuItem->addChild($menuItem);
		} elseif ($parentMenuItem instanceof MenuItem) {
			$parentMenuItem->addMenuItem($menuItem);
		} else {
			throw new HarmonyException('$parentMenuItem must be an instance of Menu or MenuItem.');
		}
		return $menuItem;
	}
}