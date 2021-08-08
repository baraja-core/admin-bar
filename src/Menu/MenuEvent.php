<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Menu;


use Baraja\AdminBar\Helpers;
use Nette\Utils\Strings;

final class MenuEvent extends MenuItem
{
	private string $label;

	private string $event;


	public function __construct(string $label, string $event)
	{
		if ($event === '') {
			throw new \InvalidArgumentException('Event is required.');
		}
		if (preg_match('/^[a-zA-Z0-9-.]+$/', $event) !== 1) {
			throw new \InvalidArgumentException(
				'Event format is not valid, because haystack "' . $event . '" given.',
			);
		}

		$this->label = Strings::firstUpper($label);
		$this->event = $event;
	}


	public function render(): string
	{
		return '<a onclick="eventBus.$emit(\'' . Helpers::escapeHtml($this->getEvent()) . '\')">'
			. Helpers::escapeHtml($this->getLabel())
			. '</a>';
	}


	public function getLabel(): string
	{
		return $this->label;
	}


	public function getEvent(): string
	{
		return $this->event;
	}
}
