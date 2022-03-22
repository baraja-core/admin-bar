<?php

declare(strict_types=1);

namespace Baraja\AdminBar;

final class ServerHelper
{
	public static function get(string $key): mixed
	{
		return $_SERVER[$key] ?? null;
	}


	public static function empty(string $key): bool
	{
		return !isset($_SERVER[$key]) || (string) $_SERVER[$key] === '';
	}


	public static function lowerEqual(string $key, string $value): bool
	{
		return isset($_SERVER[$key]) && strtolower($_SERVER[$key]) === $value;
	}
}
