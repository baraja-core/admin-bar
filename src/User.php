<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


interface User
{
	public function isLoggedIn(): bool;

	public function getName(): ?string;

	public function getAvatarUrl(): ?string;

	public function isAdmin(): bool;
}
