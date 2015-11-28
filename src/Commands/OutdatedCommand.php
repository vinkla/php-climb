<?php

/*
 * This file is part of Climb.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Climb\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vinkla\Climb\Ladder;

/**
 * This is the outdated command class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 * @author Jens Segers <hello@jenssegers.com>
 */
class OutdatedCommand extends Command
{
    /**
     * Configure the outdated command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('outdated');
        $this->setDescription('Find newer versions of dependencies than what your composer.json allows');
        $this->addOption('directory', null, InputOption::VALUE_REQUIRED, 'Composer files directory');
        $this->addOption('global', 'g', InputOption::VALUE_NONE, 'Run on globally installed packages');
        $this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format', 'console');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $formatClass = '\\Vinkla\\Climb\\Formatter\\'.ucfirst($input->getOption('format'));
        if (!class_exists($formatClass)) {
            $output->error(sprintf('Output format "%s" is not supported', $input->getOption('format')));

            return 1;
        }

        $composerPath = $this->getComposerPathFromInput($input);

        $ladder = new Ladder($composerPath);

        $packages = $ladder->getOutdatedPackages();

        $outdated = [];
        $upgradable = [];

        foreach ($packages as $package) {
            if ($package->isUpgradable()) {
                $upgradable[] = [
                    $package->getName(),
                    $package->getVersion(),
                    $package->getLatestVersion(),
                ];
            } else {
                $outdated[] = [
                    $package->getName(),
                    $package->getVersion(),
                    $package->getLatestVersion(),
                ];
            }
        }

        $outputHandler = new $formatClass();
        $outputHandler->render($output, $outdated, $upgradable);

        return 0;
    }
}
