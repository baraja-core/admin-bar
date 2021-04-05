<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Plugin;


interface Plugin
{
	public function render(): string;
}
