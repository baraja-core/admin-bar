<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Nette\DI\CompilerExtension;

final class AdminBarExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition('adminBar.basicPanel')
			->setFactory(BasicPanel::class)
			->setAutowired(BasicPanel::class);
	}
}
