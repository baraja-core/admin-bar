<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Panel;


use Baraja\AdminBar\AdminBar;
use Baraja\Localization\Localization;
use Baraja\Url\Url;
use Nette\Security\User;

final class BasicPanel implements Panel
{
	private ?string $defaultLocale = null;


	public function __construct(
		private User $user,
		private ?Localization $localization = null,
	) {
	}


	public function setDefaultLocale(string $locale): void
	{
		$this->defaultLocale = $locale;
	}


	public function getTab(): string
	{
		$baseUrl = Url::get()->getBaseUrl();
		if ($this->localization !== null) {
			$default = $this->localization->getDefaultLocale();
			$current = $this->localization->getLocale();
			$localeParam = $default !== $current ? sprintf('?locale=%s', urlencode($current)) : '';
		} else {
			$localeParam = $this->defaultLocale !== null ? sprintf('?locale=%s', urlencode($this->defaultLocale)) : '';
		}

		$buttons = [];
		$buttons[] = sprintf('<a href="%s" class="btn btn-primary">Home</a>', $baseUrl . $localeParam);
		if (\class_exists('\Baraja\Cms\Admin')) {
			$buttons[] = sprintf('<a href="%s" class="btn btn-primary">Admin</a>', sprintf('%s/admin%s', $baseUrl, $localeParam));
		}

		$apiDoc = $this->processApiDocumentation($baseUrl);
		if ($apiDoc !== null) {
			$buttons[] = $apiDoc;
		}
		if (AdminBar::getBar()->isDebugMode()) {
			$buttons[] = sprintf('<a href="%s" class="btn btn-danger">Cancel Debug mode</a>', $this->getUrlWithoutDebugMode());
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

		return sprintf('<a href="%s" class="btn btn-primary" target="_blank">API&nbsp;Doc</a>', $baseUrl . '/api-documentation');
	}


	private function getUrlWithoutDebugMode(): string
	{
		$url = new \Nette\Http\Url(Url::get()->getUrlScript());
		$url->setQueryParameter('debugMode', null);

		return $url->getAbsoluteUrl();
	}
}
