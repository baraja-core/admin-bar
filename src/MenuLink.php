<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Nette\Utils\Strings;
use Nette\Utils\Validators;

final class MenuLink
{

	/** @var string */
	private $label;

	/** @var string */
	private $url;


	public function __construct(string $label, string $url)
	{
		if (Validators::isUrl($url) === false) {
			throw new \InvalidArgumentException('URL is not valid, because "' . $url . '" given.');
		}

		$this->label = Strings::firstUpper($label);
		$this->url = $url;
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
