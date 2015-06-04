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
 * Compile model normalizers
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddModelNormalizerPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isModelNormalizerEnabled()) {
            if (count($container->findTaggedServiceIds('model_normalizer'))) {
                throw new \RuntimeException('Could not compile model normalizer, because system is not enabled.');
            }

            return;
        }

        $normalizerManagerDefinition = $container->getDefinition('model_normalizer');

        foreach ($container->findTaggedServiceIds('model_normalizer') as $id => $tags) {
            $attributes = $this->fixAttributes($tags);

            $attributes += [
                'priority' => 0
            ];

            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            if ($definition->isAbstract()) {
                // Can not compile abstract service
                continue;
            }

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);
                $requiredInterface = 'FivePercent\Component\ModelNormalizer\ModelNormalizerInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The model normalizer should implement "%s" interface.',
                        $requiredInterface
                    ));
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile model normalizer with service id "%s".',
                    $id
                ), 0, $e);
            }

            $normalizerManagerDefinition->addMethodCall('addNormalizer', [
                new Reference($id),
                $attributes['priority']
            ]);
        }
    }
}
