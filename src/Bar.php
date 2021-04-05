<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Baraja\AdminBar\Menu\Menu;
use Baraja\AdminBar\Panel\Panel;
use Baraja\AdminBar\Plugin\Plugin;
use Baraja\AdminBar\User\User;
use Baraja\Url\Url;
use Nette\Utils\FileSystem;

final class Bar
{
	private Menu $menu;

	private ?User $user = null;

	/** @var Panel[] */
	private array $panels = [];

	/** @var Plugin[] */
	private array $plugins = [];

	private bool $debugMode;

	private bool $enableVue = false;


	public function __construct()
	{
		$this->menu = new Menu;
		$this->debugMode = ($_GET['debugMode'] ?? '') === '1';
	}


	public function addPanel(Panel $panel, ?string $id = null): self
	{
		if ($id === null) {
			$c = 0;
			do {
				$id = get_class($panel) . ($c++ ? '-' . $c : '');
			} while (isset($this->panels[$id]));
		}
		$this->panels[$id] = $panel;

		return $this;
	}


	public function addPlugin(Plugin $plugin): self
	{
		$this->plugins[] = $plugin;

		return $this;
	}


	/**
	 * @return Panel[]
	 */
	public function getPanels(): array
	{
		return $this->panels;
	}


	public function getPanel(string $id): ?Panel
	{
		return $this->panels[$id] ?? null;
	}


	public function render(): void
	{
		usort($this->panels, static function (Panel $a, Panel $b): int {
			$left = $a->getPriority();
			if ($left < 0 || $left > 100) {
				throw new \LogicException('Priority must be in interval (0; 100), but "' . $left . '" given.');
			}

			return $left > $b->getPriority() ? 1 : -1;
		});

		ob_start(static function () {
		});
		try {
			$args = [
				'basePath' => Url::get()->getBaseUrl(),
				'user' => $this->user,
				'panels' => $this->panels,
				'plugins' => $this->plugins,
				'menuLinks' => $this->menu->getItems(),
				'debugMode' => $this->debugMode,
				'enableVue' => $this->enableVue,
			];

			/** @phpstan-ignore-next-line */
			extract($args, EXTR_OVERWRITE);

			require __DIR__ . '/assets/content.phtml';

			$return = ob_get_clean();
		} catch (\Throwable $e) {
			ob_end_clean();
			throw $e;
		}

		echo $return . '<style>' . Helpers::minifyHtml(FileSystem::read(__DIR__ . '/assets/style.css')) . '</style>';
	}


	public function getMenu(): Menu
	{
		return $this->menu;
	}


	public function getUser(): ?User
	{
		return $this->user;
	}


	public function setUser(?User $user): void
	{
		$this->user = $user;
	}


	public function isDebugMode(): bool
	{
		return $this->debugMode;
	}


	public function setDebugMode(bool $debugMode): void
	{
		$this->debugMode = $debugMode;
	}


	public function isEnableVue(): bool
	{
		return $this->enableVue;
	}


	public function setEnableVue(bool $enableVue = true): void
	{
		$this->enableVue = $enableVue;
	}
}
