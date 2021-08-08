<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Menu;


abstract class MenuItem implements \Stringable
{
	final public function __toString(): string
	{
		return $this->render();
	}


	/**
	 * Safe render of HTML content.
	 */
	abstract public function render(): string;
}
