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
 * Add var tag constraint factory pass
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddVarTagConstraintFactoryPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isVarTagValidatorEnabled()) {
            if (count($container->findTaggedServiceIds('validator.var_tag.constraint_factory')) > 0) {
                throw new \RuntimeException(
                    'Could not compile var tag validator constraint factory, because system is not enabled.'
                );
            }

            return;
        }

        $registryDefinition = $container->getDefinition('validator.var_tag.constraint_factory_registry');

        foreach ($container->findTaggedServiceIds('validator.var_tag.constraint_factory') as $id => $tags) {
            $factoryDefinition = $container->getDefinition($id);

            $class = $factoryDefinition->getClass();

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);

                $requiredInterface = 'FivePercent\Component\VarTagValidator\Constraint\ConstraintFactoryInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The var tag constraint factory should implement "%s" interface.',
                        $requiredInterface
                    ));
                }

                foreach ($tags as $attributes) {
                    if (empty($attributes['type'])) {
                        throw new \RuntimeException('Missing attribute "type".');
                    }

                    $registryDefinition->addMethodCall('addConstraintFactory', [
                        $attributes['type'],
                        new Reference($id)
                    ]);
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile var tag validator constraint factory with service id "%s".',
                    $id
                ));
            }
        }
    }
}
