<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Panel;


use Baraja\Localization\Localization;
use Baraja\Url\Url;
use Nette\Security\User;

final class BasicPanel implements Panel
{
	public function __construct(
		private Localization $localization,
		private User $user,
	) {
	}


	public function getTab(): string
	{
		$default = $this->localization->getDefaultLocale();
		$current = $this->localization->getLocale() ?? $default;

		return '<a href="' . ($baseUrl = Url::get()->getBaseUrl()) . ($default !== $current ? '?locale=' . $current : '') . '" class="btn btn-primary">Home</a>'
			. '&nbsp;&nbsp;&nbsp;'
			. '<a href="' . $baseUrl . '/admin' . ($default !== $current ? '?locale=' . $current : '') . '" class="btn btn-primary">Admin</a>'
			. $this->processApiDocumentation((string) $baseUrl);
	}


	public function getBody(): ?string
	{
		return null;
	}


	public function getPriority(): int
	{
		return 1;
	}


	/**
	 * Render link to API Documentation if package was installed.
	 */
	private function processApiDocumentation(string $baseUrl): ?string
	{
		if (
			\class_exists('\Baraja\StructuredApi\Doc\Documentation') === false
			|| $this->user->isLoggedIn() === false
			|| (
				$this->user->isInRole('admin') === false
				&& $this->user->isInRole('api-developer') === false
			)
		) {
			return null;
		}

		return '&nbsp;&nbsp;&nbsp;<a href="' . $baseUrl . '/api-documentation" class="btn btn-primary" target="_blank">API&nbsp;Doc</a>';
	}
}
