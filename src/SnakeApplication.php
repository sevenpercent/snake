<?php

namespace SevenPercent\Snake;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnakeApplication extends Application {

	public function __construct() {
		parent::__construct('Snake', '1.0');
	}

	protected function getCommandName(InputInterface $input) {
		return 'run';
	}

	protected function getDefaultCommands() {
		$defaultCommands = parent::getDefaultCommands();
		$defaultCommands[] = new Command\Run();
		return $defaultCommands;
	}

	public function getDefinition() {
		$inputDefinition = parent::getDefinition();
		$inputDefinition->setArguments();
		return $inputDefinition;
	}
}
