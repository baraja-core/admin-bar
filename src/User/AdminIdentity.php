<?php

declare(strict_types=1);

namespace Baraja\AdminBar\User;


use Nette\Security\SimpleIdentity;
use Nette\Utils\Validators;

final class AdminIdentity extends SimpleIdentity
{
	private ?string $name;

	private ?string $avatarUrl = null;


	public function __construct(
		int|string $id,
		?array $roles = null,
		?iterable $data = null,
		?string $name = null,
		?string $avatarUrl = null
	) {
		parent::__construct($id, $roles, $data);
		$this->name = $name;
		if ($avatarUrl !== null) {
			if (Validators::isUrl($avatarUrl) === false) {
				throw new \InvalidArgumentException('Avatar URL is not valid URL, because "' . $avatarUrl . '" given.');
			}
			$this->avatarUrl = $avatarUrl;
		}
	}


	public function getName(): ?string
	{
		return $this->name;
	}


	public function getAvatarUrl(): ?string
	{
		return $this->avatarUrl;
	}
}
