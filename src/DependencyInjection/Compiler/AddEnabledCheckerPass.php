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
 * Add checkers to enabled checker system
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddEnabledCheckerPass implements CompilerPassInterface
{
    use AttributesFixerTrait;

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
        if (!$this->integrationExtension->isEnabledCheckerEnabled()) {
            if (count($container->findTaggedServiceIds('enabled_checker')) > 0) {
                throw new \RuntimeException('Could not compile enabled checker, because system is not enabled.');
            }

            return;
        }

        if ($container->hasDefinition('enabled_checker.checker_registry')) {
            $debugRegistry = $container->getDefinition('enabled_checker.checker_registry');
        }

        $chainCheckerDefinition = $container->getDefinition('enabled_checker.checker_chain');

        foreach ($container->findTaggedServiceIds('enabled_checker') as $id => $tags) {
            $attributes = $this->fixAttributes($tags);

            $attributes += [
                'priority' => 0
            ];

            $checkerDefinition = $container->getDefinition($id);
            $class = $checkerDefinition->getClass();

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);
                $requiredInterface = 'FivePercent\Component\EnabledChecker\Checker\CheckerInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The enabled checker should implement "%s" interface.',
                        $requiredInterface
                    ));
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile enabled checker with service id "%s".',
                    $id
                ), 0, $e);
            }

            $chainCheckerDefinition->addMethodCall('addChecker', [
                new Reference($id),
                $attributes['priority']
            ]);

            if (isset($debugRegistry)) {
                $debugRegistry->addMethodCall('addChecker', [
                    $id,
                    $class,
                    $attributes['priority']
                ]);
            }
        }
    }
}
