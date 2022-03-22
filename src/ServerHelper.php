<?php

declare(strict_types=1);

namespace Baraja\AdminBar;

final class ServerHelper
{
	public static function get(string $key): ?string
	{
		$serverValue = filter_input(INPUT_SERVER, strtoupper($key), FILTER_SANITIZE_STRING);

		return $serverValue !== null && $serverValue !== false
			? $serverValue
			: null;
	}


	public static function empty(string $key): bool
	{
		$serverValue = self::get($key);

		return $serverValue === null || $serverValue === '';
	}


	public static function lowerEqual(string $key, string $value): bool
	{
		$serverValue = self::get($key);

		return is_string($serverValue) !== false && strtolower($serverValue) === $value;
	}
}
