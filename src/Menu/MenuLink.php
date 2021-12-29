<?php

declare(strict_types=1);

namespace Baraja\AdminBar\Menu;


use Baraja\AdminBar\Helpers;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

final class MenuLink extends MenuItem
{
	private string $label;

	private string $url;


	public function __construct(string $label, string $url)
	{
		$url = trim((string) preg_replace('/\s+/', '', $url));
		if (Validators::isUrl($url) === false) {
			throw new \InvalidArgumentException(
				sprintf('URL is not valid, because "%s" (length: %d bytes, base64: "%s") given.',
					$url,
					strlen($url),
					base64_encode($url),
				),
			);
		}

		$this->label = Strings::firstUpper($label);
		$this->url = $url;
	}


	public function render(): string
	{
		return sprintf('<a href="%s">%s</a>', $this->getUrl(), Helpers::escapeHtml($this->getLabel()));
	}


	public function getLabel(): string
	{
		return $this->label;
	}


	public function getUrl(): string
	{
		return $this->url;
	}
}
