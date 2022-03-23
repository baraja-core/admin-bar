<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Menu;


final class Menu
{
	/** @var array<string, array<int, MenuItem|null>> */
	private array $items = [];


	public function addLink(string $label, string $url, ?string $group = null): void
	{
		$this->addItem(new MenuLink($label, $url), $group);
	}


	public function addEvent(string $label, string $event, ?string $group = null): void
	{
		$this->addItem(new MenuEvent($label, $event), $group);
	}


	public function addItem(MenuItem $item, ?string $group = null): void
	{
		try {
			$this->items[$this->registerGroup($group)][] = $item;
		} catch (\InvalidArgumentException $e) {
			trigger_error(__METHOD__ . ': ' . $e->getMessage());
		}
	}


	public function addSeparator(?string $group = null): void
	{
		$this->items[$this->registerGroup($group)][] = null;
	}


	/**
	 * @return array<string, array<int, MenuItem|null>>
	 */
	public function getItems(): array
	{
		return $this->items;
	}


	private function registerGroup(?string $group): string
	{
		$group = $group !== null && $group !== '' ? $group : 'default';
		if (isset($this->items[$group]) === false) {
			$this->items[$group] = [];
		}

		return $group;
	}
}
