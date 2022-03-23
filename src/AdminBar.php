<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Baraja\AdminBar\User\AdminIdentity;
use Baraja\Url\Url;
use Nette\Security\User;
use Tracy\Debugger;
use Tracy\ILogger;

final class AdminBar
{
	public const
		MODE_ENABLED = true,
		MODE_DISABLED = false,
		MODE_AUTODETECT = null;

	/** @var int size of reserved memory */
	public static int $reservedMemorySize = 500_000;

	private static bool $enabled = false;

	/** @var string|null reserved memory; also prevents double rendering */
	private static ?string $reserved = null;

	private static ?Bar $bar = null;

	private static ?User $netteUser = null;


	/**
	 * @param bool|null $enabled use constant AdminBar::MODE_*
	 */
	public static function enable(?bool $enabled = self::MODE_AUTODETECT): void
	{
		$httpXRequestedWith = filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH', FILTER_UNSAFE_RAW);
		if (
			PHP_SAPI === 'cli' // cli mode
			|| ( // ajax request
				is_string($httpXRequestedWith)
				&& strtolower($httpXRequestedWith) === 'xmlhttprequest'
			)
			|| ( // api request
				class_exists(Url::class)
				&& str_starts_with(Url::get()->getUrlScript()->getPathInfo(), 'api/')
			)
		) {
			$enabled = self::MODE_DISABLED;
		}
		if (
			$enabled === self::MODE_AUTODETECT
			&& self::$netteUser !== null
			&& self::$netteUser->isLoggedIn()
			&& self::$netteUser->getIdentity() instanceof AdminIdentity
		) {
			$enabled = self::MODE_ENABLED;
		}
		self::$enabled = $enabled === true;
		if (self::$enabled === self::MODE_DISABLED) {
			return;
		}

		self::$reserved = str_repeat('b', self::$reservedMemorySize);
		register_shutdown_function([self::class, 'shutdownHandler']);
	}


	public static function getBar(): Bar
	{
		return self::$bar ?? self::$bar = new Bar;
	}


	public static function isEnabled(): bool
	{
		return self::$enabled;
	}


	public static function isReserved(): bool
	{
		return self::$reserved !== null;
	}


	public static function setNetteUser(User $user): void
	{
		self::$netteUser = $user;
	}


	public static function getNetteUser(): ?User
	{
		return self::$netteUser;
	}


	/**
	 * @internal
	 */
	public static function shutdownHandler(): void
	{
		if (self::$reserved === null || self::$enabled === self::MODE_DISABLED || Helpers::isHtmlMode() === false) {
			return;
		}
		self::$reserved = null;

		try {
			self::getBar()->render();
		} catch (\Throwable $e) {
			if (class_exists(Debugger::class)) {
				Debugger::log($e, ILogger::EXCEPTION);
			}
			echo 'Can not render admin bar.';
		}
	}
}
