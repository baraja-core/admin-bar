<?php

declare(strict_types=1);

namespace Baraja\AdminBar\User;


interface User
{
	public function isLoggedIn(): bool;

	public function getName(): ?string;

	public function getAvatarUrl(): ?string;

	public function isAdmin(): bool;
}
