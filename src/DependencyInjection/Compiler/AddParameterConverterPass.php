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
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add parameter converters
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddParameterConverterPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isParameterConverterEnabled()) {
            if (count($container->findTaggedServiceIds('converter.parameter')) > 0) {
                throw new \RuntimeException('Could not compile parameter converter, because system is not enabled.');
            }

            return;
        }

        $converterManagerDefinition = $container->getDefinition('converter.parameter');
        $converterByGroups = [
            'default' => []
        ];

        foreach ($container->findTaggedServiceIds('converter.parameter') as $id => $tags) {
            $converterDefinition = $container->getDefinition($id);

            $class = $converterDefinition->getClass();

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);
                $requiredInterface = 'FivePercent\Component\Converter\Parameter\ParameterConverterInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The parameter converter should implement "%s" interface.',
                        $requiredInterface
                    ));
                }

                if (count($tags) == 1) {
                    // Get first tag and check "group" parameter.
                    $attributes = $tags[0];

                    if (empty($attributes['group'])) {
                        $converterByGroups['default'][$id] = $converterDefinition;

                        continue;
                    }
                }

                foreach ($tags as $index => $attributes) {
                    if (empty($attributes['group'])) {
                        throw new \RuntimeException(sprintf(
                            'The attribute "group" is required if you many "converter.parameter" tag name. ' .
                            'Index tag: "%d".',
                            $index
                        ));
                    }

                    if (!isset($converterByGroups[$attributes['group']])) {
                        $converterByGroups[$attributes['group']] = [];
                    }

                    $converterByGroups[$attributes['group']][$id] = $converterDefinition;
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile parameter converter with service id "%s".',
                    $id
                ), 0, $e);
            }
        }

        foreach ($converterByGroups as $groupName => $converters) {
            // Create a chain converter for group
            $chainConverter = new DefinitionDecorator('converter.parameter.chain.abstract');
            $chainConverterId = 'converter.parameter.chain.' . $groupName;

            $container->setDefinition($chainConverterId, $chainConverter);

            foreach ($converters as $id => $converter) {
                $chainConverter->addMethodCall('addConverter', [
                    new Reference($id)
                ]);
            }

            $converterManagerDefinition->addMethodCall('setConverter', [
                new Reference($chainConverterId),
                $groupName
            ]);
        }
    }
}
