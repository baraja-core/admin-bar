<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Nette\Utils\FileSystem;

final class AdminBar
{
	public const MODE_ENABLED = true;

	public const MODE_DISABLED = false;

	public const MODE_AUTODETECT = null;

	/** @var int size of reserved memory */
	public static int $reservedMemorySize = 500000;

	private static bool $enabled = false;

	/** @var string|null reserved memory; also prevents double rendering */
	private static ?string $reserved = null;

	private static ?User $user = null;

	/** @var Panel[] */
	private static array $panels = [];

	/** @var true[] (type => true) */
	private static array $panelTypes = [];

	/** @var MenuLink[]|null[] */
	private static array $menuLinks = [];


	/**
	 * @param bool|null $enabled use constant AdminBar::MODE_*
	 */
	public static function enable(?bool $enabled = self::MODE_AUTODETECT): void
	{
		if (PHP_SAPI === 'cli') { // cli mode
			$enabled = false;
		}
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') { // ajax request
			$enabled = false;
		}
		if (Helpers::isHtmlMode() === false) { // render only to HTML
			$enabled = false;
		}
		if ((self::$enabled = $enabled === true) === self::MODE_DISABLED) {
			return;
		}

		self::$reserved = str_repeat('b', self::$reservedMemorySize);
		register_shutdown_function([__CLASS__, 'shutdownHandler']);
	}


	public static function isEnabled(): bool
	{
		return self::$enabled;
	}


	public static function addPanel(Panel $panel): void
	{
		if (isset(self::$panelTypes[$type = \get_class($panel)]) === true) {
			throw new \InvalidArgumentException('Panel "' . $type . '" is already registered. Did you use circular reference?');
		}

		self::$panelTypes[$type] = true;
		self::$panels[] = $panel;
	}


	public static function addLink(string $label, string $url): void
	{
		self::$menuLinks[] = new MenuLink($label, $url);
	}


	public static function addSeparator(): void
	{
		self::$menuLinks[] = null;
	}


	public static function setUser(User $user): void
	{
		self::$user = $user;
	}


	/**
	 * @internal
	 */
	public static function shutdownHandler(): void
	{
		self::$reserved = null;
		if (self::$enabled === self::MODE_DISABLED) {
			return;
		}

		try {
			echo self::render();
		} catch (\Throwable $e) {
			echo 'Can not render admin bar.';
		}
	}


	/**
	 * @throws \Throwable
	 */
	private static function render(): string
	{
		usort(self::$panels, static function (Panel $a, Panel $b): int {
			if (($left = $a->getPriority()) < 0 || $left > 100) {
				throw new \LogicException('Priority must be in interval (0; 100), but "' . $left . '" given.');
			}

			return $left > $b->getPriority() ? 1 : -1;
		});

		ob_start(static function () {});
		try {
			[$basePath, $user, $panels, $menuLinks] = [Helpers::getBaseUrl(), self::$user, self::$panels, self::$menuLinks];
			require __DIR__ . '/assets/content.phtml';

			$return = ob_get_clean();
		} catch (\Throwable $e) {
			ob_end_clean();
			throw $e;
		}

		return $return . '<style>' . Helpers::minifyHtml(FileSystem::read(__DIR__ . '/assets/style.css')) . '</style>';
	}
}
