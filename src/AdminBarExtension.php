<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Baraja\AdminBar\Panel\BasicLocalePanel;
use Baraja\Localization\Localization;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\Security\User;

final class AdminBarExtension extends CompilerExtension
{
	public function hasLocalization(ContainerBuilder $builder): bool
	{
		return class_exists(Localization::class) && $builder->hasDefinition('localization');
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if ($this->hasLocalization($builder)) {
			$builder->addDefinition($this->prefix('basicLocalPanel'))
				->setFactory(BasicLocalePanel::class)
				->setAutowired(BasicLocalePanel::class);
		}
	}


	public function afterCompile(ClassType $class): void
	{
		if (PHP_SAPI === 'cli') {
			return;
		}

		$builder = $this->getContainerBuilder();

		/** @var ServiceDefinition $netteUser */
		$netteUser = $builder->getDefinitionByType(User::class);

		$args = [
			$netteUser->getName(),
		];

		if ($this->hasLocalization($builder)) {
			/** @var ServiceDefinition $basicPanel */
			$basicPanel = $builder->getDefinitionByType(BasicLocalePanel::class);
			$args[] = $basicPanel->getName();
		}

		$class->getMethod('initialize')->addBody(
			'// admin bar.' . "\n"
			. '(function () {' . "\n"
			. "\t" . AdminBar::class . '::setNetteUser($this->getService(?));' . "\n"
			. ($this->hasLocalization($builder) ? "\t" . AdminBar::class . '::getBar()->addPanel($this->getService(?));' . "\n" : '')
			. "\t" . AdminBar::class . '::enable();' . "\n"
			. '})();',
			$args,
		);
	}
}
