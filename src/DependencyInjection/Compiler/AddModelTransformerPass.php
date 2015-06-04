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
 * Compile model transformers
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddModelTransformerPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isModelTransformerEnabled()) {
            if (count($container->findTaggedServiceIds('model_transformer'))) {
                throw new \RuntimeException('Could not compile model transformer, because system is not enabled.');
            }

            return;
        }

        $transformerManagerDefinition = $container->getDefinition('model_transformer');

        foreach ($container->findTaggedServiceIds('model_transformer') as $id => $tags) {
            $attributes = $this->fixAttributes($tags);

            $attributes += [
                'priority' => 0
            ];

            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);
                $requiredInterface = 'FivePercent\Component\ModelTransformer\ModelTransformerInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The model transformer should implement "%s" interface.',
                        $requiredInterface
                    ));
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile model transformer with service id "%s".',
                    $id
                ), 0, $e);
            }

            $transformerManagerDefinition->addMethodCall('addTransformer', [
                new Reference($id),
                $attributes['priority']
            ]);
        }
    }
}
