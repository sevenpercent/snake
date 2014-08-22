<?php

namespace SevenPercent\Snake\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Run extends Command {

	const FILENAME_SCRIPT = 'snakefile';

	private $_lines;

	protected function configure() {
		$this->setName('run')->addArgument('targets', InputArgument::IS_ARRAY, 'Specify build target(s)');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if (is_file(self::FILENAME_SCRIPT) && is_readable(self::FILENAME_SCRIPT)) {
			$this->_lines = file(self::FILENAME_SCRIPT);
			$exitCode = 0;
			foreach ($input->getArgument('targets') as $target) {
				if (($exitCode = $this->_executeTarget($target, $output)) !== 0) {
					break;
				}
			}
			return $exitCode;
		} else {
			$output->writeln('<error>No ' . self::FILENAME_SCRIPT . ' found in current directory</error>');
			return 1;
		}
	}

	private function _executeTarget($target, OutputInterface $output) {
		for ($i = 0; $i < count($this->_lines); $i++) {
			if (preg_match('/^' . preg_quote($target, '/') . '\s*:\s*(\w*)$/', $this->_lines[$i], $matches) === 1) {
				if (($dependencies = explode(' ', $matches[1])) !== array()) {
					foreach ($dependencies as $dependency) {
						$this->_executeTarget($dependency, $output);
					}
				}
				++$i;
				while (preg_match('/^\t(@?)(.+)$/', $this->_lines[$i], $matches) === 1) {
					if (substr($matches[2], -1) === '\\') {
						do {
							if (preg_match('/^\t@?(.+)$/', $this->_lines[++$i], $submatches) === 1) {
								$matches[2] = substr($matches[2], 0, -1) . trim($submatches[1]);
							} else {
								--$i;
								break;
							}
						} while (substr($submatches[1], -1) === '\\');
					}
					if ($matches[1] === '') {
						$output->writeln("<comment>$matches[2]</comment>");
					}
					$return = 0;
					$buffer = '';
					exec('/bin/bash -c "' . preg_replace_callback('/\${(.+)}/', function (array $matches) use ($output) {
						if (preg_match('/^call (.+)$/', $matches[1], $submatches) === 1) {
							$this->_executeTarget($submatches[1], $output);
						}
					}, strtr($matches[2], array('"' => '\"'))) . '" 2>&1', $buffer, $return);
					if ($return === 0) {
						foreach ($buffer as $line) {
							$output->writeln($line);
						}
					} else {
						foreach ($buffer as $line) {
							$output->writeln('<error>' . (substr($line, 0, 11) === '/bin/bash: ' ? substr($line, 11) : $line) . '</error>');
						}
						return $return;
					}
					++$i;
					if ($i === count($this->_lines)) {
						break;
					}
				}
				break;
			}
		}
		return 0;
	}
}
