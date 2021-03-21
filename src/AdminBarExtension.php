<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Baraja\AdminBar\Panel\BasicPanel;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\Security\User;

final class AdminBarExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('basicPanel'))
			->setFactory(BasicPanel::class)
			->setAutowired(BasicPanel::class);
	}


	public function afterCompile(ClassType $class): void
	{
		if (PHP_SAPI === 'cli') {
			return;
		}

		$builder = $this->getContainerBuilder();

		/** @var ServiceDefinition $basicPanel */
		$basicPanel = $builder->getDefinitionByType(BasicPanel::class);

		/** @var ServiceDefinition $netteUser */
		$netteUser = $builder->getDefinitionByType(User::class);

		$class->getMethod('initialize')->addBody(
			'// admin bar.' . "\n"
			. '(function () {' . "\n"
			. "\t" . AdminBar::class . '::setNetteUser($this->getService(?));' . "\n"
			. "\t" . AdminBar::class . '::getBar()->addPanel($this->getService(?));' . "\n"
			. "\t" . AdminBar::class . '::enable();' . "\n"
			. '})();',
			[
				$netteUser->getName(),
				$basicPanel->getName(),
			],
		);
	}
}
