<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Panel;


use Baraja\AdminBar\AdminBar;
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
		$baseUrl = Url::get()->getBaseUrl();

		$buttons = [];
		$buttons[] = '<a href="' . $baseUrl . ($default !== $current ? '?locale=' . $current : '') . '" class="btn btn-primary">Home</a>';
		$buttons[] = '<a href="' . $baseUrl . '/admin' . ($default !== $current ? '?locale=' . $current : '') . '" class="btn btn-primary">Admin</a>';

		$apiDoc = $this->processApiDocumentation($baseUrl);
		if ($apiDoc !== null) {
			$buttons[] = $apiDoc;
		}
		if (AdminBar::getBar()->isDebugMode()) {
			$buttons[] = '<a href="' . $this->getUrlWithoutDebugMode() . '" class="btn btn-danger">Cancel Debug mode</a>';
		}

		return implode('&nbsp;&nbsp;&nbsp;', $buttons);
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

		return '<a href="' . $baseUrl . '/api-documentation" class="btn btn-primary" target="_blank">API&nbsp;Doc</a>';
	}


	private function getUrlWithoutDebugMode(): string
	{
		$url = new \Nette\Http\Url(Url::get()->getUrlScript());
		$url->setQueryParameter('debugMode', null);

		return $url->getAbsoluteUrl();
	}
}
