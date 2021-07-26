<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Menu;


final class Menu
{
	/** @var array<string, array<int, MenuLink|null>> */
	private array $items = [];


	public function addLink(string $label, string $url, ?string $group = null): void
	{
		try {
			$this->items[$this->registerGroup($group)][] = new MenuLink($label, $url);
		} catch (\InvalidArgumentException $e) {
			trigger_error(__METHOD__ . ': ' . $e->getMessage());
		}
	}


	public function addSeparator(?string $group = null): void
	{
		$this->items[$this->registerGroup($group)][] = null;
	}


	/**
	 * @return array<string, array<int, MenuLink|null>>
	 */
	public function getItems(): array
	{
		return $this->items;
	}


	private function registerGroup(?string $group): string
	{
		$group = $group ?: 'default';
		if (isset($this->items[$group]) === false) {
			$this->items[$group] = [];
		}

		return $group;
	}
}
