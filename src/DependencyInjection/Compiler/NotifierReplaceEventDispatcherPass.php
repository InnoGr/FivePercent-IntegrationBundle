<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler;

use FivePercent\Bundle\IntegrationBundle\DependencyInjection\IntegrationExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replace event dispatcher for notifier system
 *
 * @author Vitaliy Zhuk <zhuk22005@gmail.com>
 */
class NotifierReplaceEventDispatcherPass implements CompilerPassInterface
{
    /**
     * @var IntegrationExtension
     */
    private $integrationExtension;

    /**
     * Construct
     *
     * @param IntegrationExtension $integrationExtension
     */
    public function __construct(IntegrationExtension $integrationExtension)
    {
        $this->integrationExtension = $integrationExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$this->integrationExtension->isNotifierEnabled() || !$this->integrationExtension->isNotifierReplaceSfEventDispatcher()) {
            return;
        }

        $dispatcherServiceId = 'event_dispatcher';

        while ($container->hasAlias($dispatcherServiceId)) {
            $dispatcherServiceId = $container->getAlias($dispatcherServiceId);
        }

        $dispatcherDefinition = $container->getDefinition($dispatcherServiceId);
        $dispatcherDefinition->setPublic(false);

        $container->removeDefinition($dispatcherServiceId);
        $container->setDefinition('event_dispatcher.symfony', $dispatcherDefinition);

        $notifierDispatcherDefinition = $container->getDefinition('event_dispatcher.notifier_proxy');
        $notifierDispatcherDefinition->replaceArgument(0, new Reference('event_dispatcher.symfony'));
        $notifierDispatcherDefinition->replaceArgument(2, $this->integrationExtension->getNotifierEventNamesForDisableNotification());
        $notifierDispatcherDefinition->setAbstract(false);

        $container->setAlias('event_dispatcher', 'event_dispatcher.notifier_proxy');
    }
}
