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
				'URL is not valid, because "' . $url . '" '
				. '(length: ' . strlen($url) . ' bytes, base64: "' . base64_encode($url) . '") given.',
			);
		}

		$this->label = Strings::firstUpper($label);
		$this->url = $url;
	}


	public function render(): string
	{
		return '<a href="' . $this->getUrl() . '">' . Helpers::escapeHtml($this->getLabel()) . '</a>';
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
