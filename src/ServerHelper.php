<?php

declare(strict_types=1);

namespace Baraja\AdminBar;

final class ServerHelper
{
	/** @var array<string> */
	private array $serverVars;


	public function __construct()
	{
		$this->serverVars = $_SERVER;
	}


	public function get(string $key): ?string
	{
		return $this->serverVars[strtoupper($key)] ?? null;
	}


	public function empty(string $key): bool
	{
		$serverValue = $this->get($key);

		return $serverValue === null || $serverValue === '';
	}


	public function lowerEqual(string $key, string $value): bool
	{
		$serverValue = $this->get($key);

		return is_string($serverValue) !== false && strtolower($serverValue) === $value;
	}
}
