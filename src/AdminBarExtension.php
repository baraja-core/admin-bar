<?php

declare(strict_types=1);

namespace Baraja\AdminBar;


use Baraja\AdminBar\Panel\BasicPanel;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Security\User;
use stdClass;

final class AdminBarExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure(
			[
				'defaultLocale' => Expect::string('en'),
			],
		);
	}


	public function beforeCompile(): void
	{
		/** @var stdClass{defaultLocale: string} $config */
		$config = $this->config;

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('basicPanel'))
			->setFactory(BasicPanel::class)
			->addSetup('setDefaultLocale', [$config->defaultLocale])
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
