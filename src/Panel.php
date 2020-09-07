<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


interface Panel
{
	public function getTab(): string;

	public function getBody(): ?string;

	/**
	 * 0-100; bigger is best.
	 */
	public function getPriority(): ?int;
}
