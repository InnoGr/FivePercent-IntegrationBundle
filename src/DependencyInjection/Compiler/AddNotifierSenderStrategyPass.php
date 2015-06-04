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
 * Add notifier sender strategy
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddNotifierSenderStrategyPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isNotifierEnabled()) {
            if (count($container->findTaggedServiceIds('notifier.sender.strategy')) > 0) {
                throw new \RuntimeException(
                    'Could not compile notifier sender strategy, because system is not enabled.'
                );
            }

            return;
        }

        $strategyManagerDefinition = $container->findDefinition('notifier.sender_strategy_manager');

        foreach ($container->findTaggedServiceIds('notifier.sender.strategy') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);
                $requiredInterface = 'FivePercent\Component\Notifier\SenderStrategy\SenderStrategyInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The notifier sender strategy should implement "%s" interface.',
                        $requiredInterface
                    ));
                }

                foreach ($tags as $index => $attributes) {
                    if (empty($attributes['key'])) {
                        throw new  \RuntimeException(sprintf(
                            'The "key" parameter for tag with index "%d" must be a specified.',
                            $index
                        ));
                    }

                    $strategyManagerDefinition->addMethodCall('addStrategy', [
                        $attributes['key'],
                        new Reference($id)
                    ]);
                }

            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile notifier sender strategy with service id "%s".',
                    $id
                ), 0, $e);
            }
        }
    }
}
