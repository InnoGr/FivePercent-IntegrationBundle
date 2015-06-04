<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\Command;

use FivePercent\Bundle\IntegrationBundle\EnabledChecker\Checker\DebugCheckerRegistry;
use FivePercent\Bundle\IntegrationBundle\Util\SymfonyHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Debug enabled checkers
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class EnabledCheckerDebugCommand extends Command
{
    /**
     * @var DebugCheckerRegistry
     */
    private $checkerRegistry;

    /**
     * Construct
     *
     * @param DebugCheckerRegistry $checkerRegistry
     */
    public function __construct(DebugCheckerRegistry $checkerRegistry)
    {
        $this->checkerRegistry = $checkerRegistry;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('debug:enabled-checker')
            ->setDescription('View all enabled checkers');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkers = $this->checkerRegistry->getCheckers();

        /** @var \Symfony\Component\Console\Helper\Table $table */
        $table = $this->getHelper('table');

        $table->setHeaders(['Order', 'Bundle', 'Service', 'Class', 'Position']);

        $order = 0;
        foreach ($checkers as $entry) {
            $table->addRow([
                '#' . (++$order),
                SymfonyHelper::getBundleNameFromClass($entry['class']),
                $entry['id'],
                $entry['class'],
                $entry['priority']
            ]);
        }

        $table->render($output);

        return 0;
    }
}
