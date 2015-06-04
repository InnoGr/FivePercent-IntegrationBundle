<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * FivePercent Core configuration
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fivepercent_integration');

        $node = $rootNode->children();

        $this->configureCacheSection($node);
        $this->configureEnabledCheckerSection($node);
        $this->configureObjectMapper($node);
        $this->configureVarTagValidator($node);
        $this->configureModelTransformer($node);
        $this->configureModelNormalizer($node);
        $this->configureConverter($node);
        $this->configureNotifier($node);
        $this->configureObjectSecurity($node);
        $this->configureTransactional($node);

        return $treeBuilder;
    }

    /**
     * Configure cache system
     *
     * @param NodeBuilder $node
     */
    protected function configureCacheSection(NodeBuilder $node)
    {
        $node
            ->arrayNode('cache')
                ->addDefaultsIfNotSet()
                ->info('Cache system for FivePercent core')
                ->children()
                    ->scalarNode('doctrine_proxy')
                        ->beforeNormalization()
                            ->ifTrue()
                            ->then(function () { return 'cache.doctrine'; })
                        ->end()
                        ->defaultValue(false)
                        ->info('Add doctrine proxy adapter for use this cache in doctrine systems')
                    ->end()

                    ->arrayNode('services')
                        ->useAttributeAsKey('service')
                        ->defaultValue(['cache.array'])
                        ->info('The service ids for add to chain cache.')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end();

    }

    /**
     * Configure enabled checker system
     *
     * @param NodeBuilder $node
     */
    protected function configureEnabledCheckerSection(NodeBuilder $node)
    {
        $node
            ->arrayNode('enabled_checker')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->booleanNode('use_proxy')
                        ->defaultValue(true)
                        ->info('Use "Proxy" for Service Container')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure object mapper system
     *
     * @param NodeBuilder $node
     */
    protected function configureObjectMapper(NodeBuilder $node)
    {
        $node
            ->arrayNode('object_mapper')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->scalarNode('cache')
                        ->beforeNormalization()
                            ->ifTrue()
                            ->then(function () { return 'cache'; })
                        ->end()
                        ->defaultValue(false)
                        ->info('Cache service ID for use cached metadata factory')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure var tag system
     *
     * @param NodeBuilder $node
     */
    protected function configureVarTagValidator(NodeBuilder $node)
    {
        $node
            ->arrayNode('var_tag_validator')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->scalarNode('cache')
                        ->beforeNormalization()
                            ->ifTrue()
                            ->then(function () { return 'cache'; })
                        ->end()
                        ->defaultValue(false)
                        ->info('Cache service ID for use cached metadata factory')
                    ->end()

                    ->booleanNode('replace_validator')
                        ->defaultValue(true)
                        ->info('Replace Symfony Validator for access from "validator" service')
                    ->end()

                    ->arrayNode('aliases')
                        ->info('Aliases for types')
                        ->example(['Money' => 'double'])
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure model transformer system
     *
     * @param NodeBuilder $node
     */
    protected function configureModelTransformer(NodeBuilder $node)
    {
        $node
            ->arrayNode('model_transformer')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->arrayNode('annotated')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('cache')
                                ->beforeNormalization()
                                    ->ifTrue()
                                    ->then(function (){ return 'cache'; })
                                ->end()
                                ->info('The service id of cache service for cached metatada')
                                ->defaultValue(false)
                            ->end()
                        ->end()
                    ->end()

                    ->booleanNode('doctrine_orm_persistent_collection')
                        ->info('Enable Doctrine ORM Persistent Collection transformer')
                        ->defaultValue(false)
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure model normalizer system
     *
     * @param NodeBuilder $node
     */
    protected function configureModelNormalizer(NodeBuilder $node)
    {
        $node
            ->arrayNode('model_normalizer')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->arrayNode('annotated')
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('cache')
                                ->beforeNormalization()
                                    ->ifTrue()
                                    ->then(function (){ return 'cache'; })
                                ->end()
                                ->info('The service id of cache service for cached metadata')
                                ->defaultValue(false)
                            ->end()
                        ->end()
                    ->end()

                    ->booleanNode('money')
                        ->defaultValue(false)
                        ->info('Enable "Money" money normalizer')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure converter system
     *
     * @param NodeBuilder $node
     */
    protected function configureConverter(NodeBuilder $node)
    {
        $node
            ->arrayNode('converter')
                ->children()
                    ->arrayNode('parameter')
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultTrue()
                            ->end()

                            ->scalarNode('cache')
                                ->beforeNormalization()
                                    ->ifTrue()
                                    ->then(function () { return 'cache'; })
                                ->end()
                                ->info('Use cache service for loads metadata')
                                ->defaultNull()
                            ->end()

                            ->booleanNode('orm')
                                ->defaultValue(true)
                                ->info('Enable "ORM" parameter converter. The package Doctrine ORM must be installed.')
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('property')
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultTrue()
                            ->end()

                            ->scalarNode('cache')
                                ->beforeNormalization()
                                    ->ifTrue()
                                    ->then(function () { return 'cache'; })
                                ->end()
                                ->info('Use cache service for loads metadata')
                                ->defaultNull()
                            ->end()

                            ->booleanNode('orm')
                                ->defaultValue(true)
                                ->info('Enable "ORM" property converter. The package Doctrine ORM must be installed.')
                            ->end()

                            ->booleanNode('money')
                                ->defaultValue(false)
                                ->info('Enable "Money" property converter. The package Money must be installed.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure notifier system
     *
     * @param NodeBuilder $node
     */
    protected function configureNotifier(NodeBuilder $node)
    {
        $node
            ->arrayNode('notifier')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->scalarNode('cache')
                        ->beforeNormalization()
                            ->ifTrue()
                            ->then(function (){ return 'cache'; })
                        ->end()
                        ->info('The service ID of cache for cached metadata')
                    ->end()

                    ->scalarNode('object_data_extractor')
                        ->info('The service id of object data extractor for extract notification data.')
                        ->defaultValue('notifier.object_data_extractor.transformation_and_normalization')
                    ->end()

                    ->arrayNode('symfony_event_dispatcher')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('replace')
                                ->info('Rewrite Symfony2 Event Dispatcher for send notifications on dispatch.')
                                ->defaultValue(false)
                            ->end()

                            ->arrayNode('disabled_event_names')
                                ->info('List of not send notifications on events.')
                                ->defaultValue([
                                    // Console system events
                                    ConsoleEvents::COMMAND,
                                    ConsoleEvents::EXCEPTION,
                                    ConsoleEvents::TERMINATE,

                                    // Kernel system events
                                    KernelEvents::CONTROLLER,
                                    KernelEvents::EXCEPTION,
                                    KernelEvents::FINISH_REQUEST,
                                    KernelEvents::REQUEST,
                                    KernelEvents::RESPONSE,
                                    KernelEvents::TERMINATE,
                                    KernelEvents::VIEW
                                ])
                                ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()

                    ->arrayNode('sender')
                        ->isRequired()
                        ->children()
                            ->scalarNode('service')
                                ->info('The service id of notification sender.')
                            ->end()

                            ->arrayNode('amqp')
                                ->info('Use AMQP PHP Extension for send notifications')
                                ->children()
                                    ->scalarNode('host')
                                        ->defaultValue('127.0.0.1')
                                        ->info('Host for connection')
                                    ->end()

                                    ->scalarNode('port')
                                        ->defaultValue(5672)
                                        ->info('Port for connection')
                                    ->end()

                                    ->scalarNode('vhost')
                                        ->info('Is supported in AMQP system')
                                        ->example('/foo')
                                        ->defaultValue('/')
                                    ->end()

                                    ->scalarNode('login')
                                        ->info('Login for connection')
                                        ->example('user')
                                    ->end()

                                    ->scalarNode('password')
                                        ->info('Password for connection')
                                        ->example('password')
                                    ->end()

                                    ->scalarNode('exchange_name')
                                        ->defaultValue('notifier')
                                        ->info('Exchange name')
                                        ->example('notifier')
                                    ->end()

                                    // @todo: add validation for exchange type
                                    ->scalarNode('exchange_type')
                                        ->defaultValue('direct')
                                        ->info('Exchange type')
                                        ->example('direct')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure object security system
     *
     * @param NodeBuilder $node
     */
    protected function configureObjectSecurity(NodeBuilder $node)
    {
        $node
            ->arrayNode('object_security')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->scalarNode('cache')
                        ->beforeNormalization()
                            ->ifTrue()
                            ->then(function (){ return 'cache'; })
                        ->end()
                        ->info('Cache service ID for cache metadata')
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Configure transactional system
     *
     * @param NodeBuilder $node
     */
    protected function configureTransactional(NodeBuilder $node)
    {
        $node
            ->arrayNode('transactional')
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                    ->end()

                    ->scalarNode('service')
                        ->info('The service id of transactional service.')
                        ->defaultValue('transactional.doctrine.orm')
                    ->end()

                    ->booleanNode('proxy_services')
                        ->info('Auto generate proxy services for transactional methods')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end();
    }
}
