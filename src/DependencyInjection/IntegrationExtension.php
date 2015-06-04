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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * FivePercent Integration extension
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class IntegrationExtension extends Extension
{
    const ALIAS = 'fivepercent_integration';

    /**
     * @var bool
     */
    private $enabledCheckerEnabled = false;

    /**
     * @var bool
     */
    private $objectMapperEnabled = false;

    /**
     * @var bool
     */
    private $varTagValidatorEnabled = false;

    /**
     * @var bool
     */
    private $varTagReplaceSfValidator = false;

    /**
     * @var bool
     */
    private $modelTransformerEnabled = false;

    /**
     * @var bool
     */
    private $modelNormalizerEnabled = false;

    /**
     * @var bool
     */
    private $parameterConverterEnabled = false;

    /**
     * @var bool
     */
    private $propertyConverterEnabled = false;

    /**
     * @var bool
     */
    private $notifierEnabled = false;

    /**
     * @var bool
     */
    private $notifierReplaceSfEventDispatcher = false;

    /**
     * @var array
     */
    private $notifierDisabledSfEventNames = [];

    /**
     * @var bool
     */
    private $objectSecurityEnabled;

    /**
     * @var bool
     */
    private $transactionalEnabled = false;

    /**
     * @var bool
     */
    private $transactionalAutoGenerateProxy = false;

    /**
     * @var string
     */
    private $transactionalService;

    /**
     * Is enabled checker system enabled
     *
     * @return bool
     */
    public function isEnabledCheckerEnabled()
    {
        return $this->enabledCheckerEnabled;
    }

    /**
     * Is object mapper enabled
     *
     * @return bool
     */
    public function isObjectMapperEnabled()
    {
        return $this->objectMapperEnabled;
    }

    /**
     * Is var tag validator enabled
     *
     * @return bool
     */
    public function isVarTagValidatorEnabled()
    {
        return $this->varTagValidatorEnabled;
    }

    /**
     * Is replace Symfony Validator for use VarTagValidator
     *
     * @return bool
     */
    public function isVarTagReplaceSfValidator()
    {
        return $this->varTagReplaceSfValidator;
    }

    /**
     * Is model transformer enabled
     *
     * @return bool
     */
    public function isModelTransformerEnabled()
    {
        return $this->modelTransformerEnabled;
    }

    /**
     * Is model normalizer enabled
     *
     * @return bool
     */
    public function isModelNormalizerEnabled()
    {
        return $this->modelNormalizerEnabled;
    }

    /**
     * Is parameter converter enabled
     *
     * @return bool
     */
    public function isParameterConverterEnabled()
    {
        return $this->parameterConverterEnabled;
    }

    /**
     * Is property converter enabled
     *
     * @return bool
     */
    public function isPropertyConverterEnabled()
    {
        return $this->propertyConverterEnabled;
    }

    /**
     * Is notifier enabled
     *
     * @return bool
     */
    public function isNotifierEnabled()
    {
        return $this->notifierEnabled;
    }

    /**
     * Is replace Symfony Event Dispatcher for use Notifier
     *
     * @return bool
     */
    public function isNotifierReplaceSfEventDispatcher()
    {
        return $this->notifierReplaceSfEventDispatcher;
    }

    /**
     * Get event names for disable notifications (Control in event dispatcher)
     *
     * @return array
     */
    public function getNotifierEventNamesForDisableNotification()
    {
        return $this->notifierDisabledSfEventNames;
    }

    /**
     * Is object security enabled
     *
     * @return bool
     */
    public function isObjectSecurityEnabled()
    {
        return $this->objectSecurityEnabled;
    }

    /**
     * Is transactional enabled
     *
     * @return bool
     */
    public function isTransactionalEnabled()
    {
        return $this->transactionalEnabled;
    }

    /**
     * Is auto generate proxy methods with transactional layer
     *
     * @return bool
     */
    public function isTransactionalAutoGenerateProxy()
    {
        return $this->transactionalAutoGenerateProxy;
    }

    /**
     * Get transactional service
     *
     * @return string
     */
    public function getTransactionalService()
    {
        return $this->transactionalService;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->registerCache($config['cache'], $container, $loader);

        if (!empty($config['enabled_checker']) && $this->isConfigEnabled($container, $config['enabled_checker'])) {
            $this->registerEnabledChecker($config['enabled_checker'], $container, $loader);
        }

        if (!empty($config['object_mapper']) && $this->isConfigEnabled($container, $config['object_mapper'])) {
            $this->registerObjectMapper($config['object_mapper'], $container, $loader);
        }

        if (!empty($config['var_tag_validator']) && $this->isConfigEnabled($container, $config['var_tag_validator'])) {
            $this->registerVarTagValidator($config['var_tag_validator'], $container, $loader);
        }

        if (!empty($config['model_transformer']) && $this->isConfigEnabled($container, $config['model_transformer'])) {
            $this->registerModelTransformer($config['model_transformer'], $container, $loader);
        }

        if (!empty($config['model_normalizer']) && $this->isConfigEnabled($container, $config['model_normalizer'])) {
            $this->registerModelNormalizer($config['model_normalizer'], $container, $loader);
        }

        if (!empty($config['converter'])) {
            $converter = $config['converter'];

            if (!empty($converter['parameter']) && $this->isConfigEnabled($container, $converter['parameter'])) {
                $this->registerParameterConverter($converter['parameter'], $container, $loader);
            }

            if (!empty($converter['property']) && $this->isConfigEnabled($container, $converter['property'])) {
                $this->registerPropertyConverter($converter['property'], $container, $loader);
            }
        }

        if (!empty($config['notifier']) && $this->isConfigEnabled($container, $config['notifier'])) {
            $this->registerNotifier($config['notifier'], $container, $loader);
        }

        if (!empty($config['object_security']) && $this->isConfigEnabled($container, $config['object_security'])) {
            $this->registerObjectSecurity($config['object_security'], $container, $loader);
        }

        if (!empty($config['transactional']) && $this->isConfigEnabled($container, $config['transactional'])) {
            $this->registerTransactional($config['transactional'], $loader);
        }
    }

    /**
     * Register cache system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerCache(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('cache.xml');

        $chainCacheDefinition = $container->getDefinition('cache.chain');

        $caches = [];

        foreach ($config['services'] as $key => $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $container->getDefinition($serviceId)->setAbstract(false);
            }

            $caches[] = new Reference($serviceId);
        }

        $chainCacheDefinition->setArguments([$caches]);
        $container->setAlias('cache', 'cache.chain');

        if ($config['doctrine_proxy']) {
            if (!interface_exists('Doctrine\Common\Cache\Cache')) {
                throw new \RuntimeException('Could not create doctrine cache adapter. Please install Doctrine Cache.');
            }

            $definition = new Definition('FivePercent\Component\Cache\DoctrineCache');
            $definition->setArguments([ new Reference('cache') ]);
            $container->setDefinition($config['doctrine_proxy'], $definition);

            $this->addClassesToCompile([
                'FivePercent\Component\Cache\DoctrineCache'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\Cache\CacheInterface',
            'FivePercent\Component\Cache\ArrayCache'
        ]);
    }

    /**
     * Register enabled checker system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerEnabledChecker(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'EnabledChecker',
            'FivePercent\Component\EnabledChecker\EnabledCheckerInterface',
            'fivepercent/enabled-checker'
        );

        $this->enabledCheckerEnabled = true;

        $loader->load('enabled_checker/enabled_checker.xml');

        if ($container->getParameter('kernel.debug')) {
            $loader->load('enabled_checker/debug.xml');
        }

        if ($config['use_proxy']) {
            $container->setAlias('enabled_checker', 'enabled_checker.proxy');
            $container->getDefinition('enabled_checker.proxy')->setAbstract(false);
        } else {
            $container->setAlias('enabled_checker', 'enabled_checker.real');
        }
    }

    /**
     * Register object mapper system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerObjectMapper(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'ObjectMapper',
            'FivePercent\Component\ObjectMapper\ObjectMapperInterface',
            'fivepercent/object-mapper'
        );

        $this->objectMapperEnabled = true;

        $loader->load('object_mapper/object_mapper.xml');

        if ($config['cache']) {
            $this->registerCachedSystemForService($container, $config['cache'], 'object_mapper.metadata_factory');

            $this->addClassesToCompile([
                'FivePercent\Component\ObjectMapper\Metadata\CachedMetadataFactory'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\ObjectMapper\Exception\ObjectNotSupportedException',
            'FivePercent\Component\ObjectMapper\Exception\StrategyNotFoundException',

            'FivePercent\Component\ObjectMapper\Metadata\Loader\AnnotationLoader',
            'FivePercent\Component\ObjectMapper\Metadata\Loader\ChainLoader',
            'FivePercent\Component\ObjectMapper\Metadata\Loader\LoaderInterface',

            'FivePercent\Component\ObjectMapper\Metadata\MetadataFactory',
            'FivePercent\Component\ObjectMapper\Metadata\MetadataFactoryInterface',
            'FivePercent\Component\ObjectMapper\Metadata\ObjectMetadata',
            'FivePercent\Component\ObjectMapper\Metadata\PropertyMetadata',

            'FivePercent\Component\ObjectMapper\Strategy\ReflectionStrategy',
            'FivePercent\Component\ObjectMapper\Strategy\StrategyInterface',
            'FivePercent\Component\ObjectMapper\Strategy\StrategyManager',
            'FivePercent\Component\ObjectMapper\Strategy\StrategyManagerInterface',

            'FivePercent\Component\ObjectMapper\ObjectMapper',
            'FivePercent\Component\ObjectMapper\ObjectMapperInterface'
        ]);
    }

    /**
     * Register var tag validator system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerVarTagValidator(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'VarTagValidator',
            'FivePercent\Component\VarTagValidator\VarTagValidatorInterface',
            'fivepercent/var-tag-validator'
        );

        $this->varTagValidatorEnabled = true;
        $this->varTagReplaceSfValidator = $config['replace_validator'];

        $loader->load('var_tag_validator/var_tag_validator.xml');

        if ($config['cache']) {
            // Use cache system
            $this->registerCachedSystemForService($container, $config['cache'], 'validator.var_tag.metadata_factory');

            $this->addClassesToCompile([
                'FivePercent\Component\VarTagValidator\Metadata\CachedMetadataFactory'
            ]);
        }

        foreach ($config['aliases'] as $alias => $type) {
            $container->getDefinition('validator.var_tag.constraint_factory_registry')
                ->addMethodCall('addConstraintFactoryAlias', [$alias, $type]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\VarTagValidator\Constraint\Factory\ArrayConstraintFactory',
            'FivePercent\Component\VarTagValidator\Constraint\Factory\DoubleConstraintFactory',
            'FivePercent\Component\VarTagValidator\Constraint\Factory\IntegerConstraintFactory',
            'FivePercent\Component\VarTagValidator\Constraint\Factory\ScalarConstraintFactory',
            'FivePercent\Component\VarTagValidator\Constraint\Factory\StringConstraintFactory',

            'FivePercent\Component\VarTagValidator\Constraint\ConstraintFactoryInterface',
            'FivePercent\Component\VarTagValidator\Constraint\FactoryRegistry',
            'FivePercent\Component\VarTagValidator\Constraint\FactoryRegistryInterface',
            'FivePercent\Component\VarTagValidator\Constraint\VarTagConstraint',

            'FivePercent\Component\VarTagValidator\Exception\ConstraintFactoryNotFoundException',

            'FivePercent\Component\VarTagValidator\Metadata\MetadataFactory',
            'FivePercent\Component\VarTagValidator\Metadata\MetadataFactoryInterface',
            'FivePercent\Component\VarTagValidator\Metadata\ClassMetadata',
            'FivePercent\Component\VarTagValidator\Metadata\PropertyMetadata'
        ]);
    }

    /**
     * Register model transformer system
     *
     * @param array         $config
     * @param XmlFileLoader $loader
     */
    private function registerModelTransformer(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'ModelTransformer',
            'FivePercent\Component\ModelTransformer\ModelTransformerManagerInterface',
            'fivepercent/model-transformer'
        );

        $this->modelTransformerEnabled = true;

        $loader->load('model_transformer/model_transformer.xml');

        if ($this->isConfigEnabled($container, $config['annotated'])) {
            $loader->load('model_transformer/annotated.xml');

            if ($config['annotated']['cache']) {
                $this->registerCachedSystemForService($container, $config['annotated']['cache'], 'model_transformer.annotated.metadata_factory');

                $this->addClassesToCompile([
                    'FivePercent\Component\ModelTransformer\Transformer\Annotated\Metadata\CachedMetadataFactory'
                ]);
            }

            $this->addClassesToCompile([
                'FivePercent\Component\ModelTransformer\Transformer\Annotated\Exception\TransformAnnotationNotFoundException',

                'FivePercent\Component\ModelTransformer\Transformer\Annotated\Metadata\MetadataFactory',
                'FivePercent\Component\ModelTransformer\Transformer\Annotated\Metadata\MetadataFactoryInterface',
                'FivePercent\Component\ModelTransformer\Transformer\Annotated\Metadata\ObjectMetadata',
                'FivePercent\Component\ModelTransformer\Transformer\Annotated\Metadata\PropertyMetadata',

                'FivePercent\Component\ModelTransformer\Transformer\Annotated\AnnotatedModelTransformer'
            ]);
        }

        if (!empty($config['doctrine_orm_persistent_collection'])) {
            $definition = new Definition($container->getParameter('model_transformer.doctrine_orm_persistent_collection.class'));
            $definition->setPublic(false);
            $definition->addTag('model_transformer', [
                'priority' => 128
            ]);

            $container->setDefinition('model_transformer.doctrine_orm_persistent_collection', $definition);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\ModelTransformer\Exception\TransformationFailedException',
            'FivePercent\Component\ModelTransformer\Exception\UnsupportedClassException',

            'FivePercent\Component\ModelTransformer\Transformer\TransformableModelTransformer',
            'FivePercent\Component\ModelTransformer\Transformer\TraversableModelTransformer',
            'FivePercent\Component\ModelTransformer\Transformer\AbstractTraversableModelTransformer',

            'FivePercent\Component\ModelTransformer\Context',
            'FivePercent\Component\ModelTransformer\ContextInterface',
            'FivePercent\Component\ModelTransformer\ModelTransformerInterface',
            'FivePercent\Component\ModelTransformer\ModelTransformerManager',
            'FivePercent\Component\ModelTransformer\ModelTransformerManagerAwareInterface',
            'FivePercent\Component\ModelTransformer\ModelTransformerManagerInterface',
            'FivePercent\Component\ModelTransformer\TransformableInterface'
        ]);
    }

    /**
     * Register model normalizer system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerModelNormalizer(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'ModelNormalizer',
            'FivePercent\Component\ModelNormalizer\ModelNormalizerManagerInterface',
            'fivepercent/model-normalizer'
        );

        $this->modelNormalizerEnabled = true;

        $loader->load('model_normalizer/model_normalizer.xml');

        if ($this->isConfigEnabled($container, $config['annotated'])) {
            $loader->load('model_normalizer/annotated.xml');

            if ($config['annotated']['cache']) {
                $this->registerCachedSystemForService($container, $config['annotated']['cache'], 'model_normalizer.annotated.metadata_factory');

                $this->addClassesToCompile([
                    'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Metadata\CachedMetadataFactory'
                ]);
            }

            $this->addClassesToCompile([
                'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Exception\NormalizeAnnotationNotFoundException',

                'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Metadata\MetadataFactory',
                'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Metadata\MetadataFactoryInterface',
                'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Metadata\ObjectMetadata',
                'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Metadata\PropertyMetadata',

                'FivePercent\Component\ModelNormalizer\Normalizer\Annotated\AnnotatedModelNormalizer'
            ]);
        }

        if ($config['money']) {
            $container->getDefinition('model_normalizer.money')->setAbstract(false);

            $this->addClassesToCompile([
                'FivePercent\Component\ModelNormalizer\Normalizer\MoneyNormalizer'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\ModelNormalizer\Exception\DenormalizationFailedException',
            'FivePercent\Component\ModelNormalizer\Exception\NormalizationFailedException',
            'FivePercent\Component\ModelNormalizer\Exception\UnsupportedClassException',

            'FivePercent\Component\ModelNormalizer\Normalizer\DateTimeNormalizer',
            'FivePercent\Component\ModelNormalizer\Normalizer\NormalizableModelNormalizer',
            'FivePercent\Component\ModelNormalizer\Normalizer\TraversableModelNormalizer',

            'FivePercent\Component\ModelNormalizer\Context',
            'FivePercent\Component\ModelNormalizer\ContextInterface',
            'FivePercent\Component\ModelNormalizer\ModelNormalizerInterface',
            'FivePercent\Component\ModelNormalizer\ModelNormalizerManager',
            'FivePercent\Component\ModelNormalizer\ModelNormalizerManagerAwareInterface',
            'FivePercent\Component\ModelNormalizer\ModelNormalizerManagerInterface',
            'FivePercent\Component\ModelNormalizer\NormalizableInterface'
        ]);

    }

    /**
     * Register parameter converter system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerParameterConverter(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'ParameterConverter',
            'FivePercent\Component\Converter\Parameter\ParameterConverterInterface',
            'fivepercent/converter'
        );

        $this->parameterConverterEnabled = true;

        $loader->load('converter/parameter/parameter_converter.xml');

        if ($config['orm']) {
            $this->checkLibraryInstalled(
                'ORM Parameter converter',
                'Doctrine\ORM\Version',
                'doctrine/orm'
            );

            $loader->load('converter/parameter/orm.xml');

            if ($config['cache']) {
                $this->registerCachedSystemForService($container, $config['cache'], 'converter.parameter.orm.reader');

                $this->addClassesToCompile([
                    'FivePercent\Component\Converter\Parameter\Converters\ORM\ORMParameterConverterCachedReader'
                ]);
            }

            $this->addClassesToCompile([
                'FivePercent\Component\Converter\Parameter\Converters\ORM\ORMParameterConverter',
                'FivePercent\Component\Converter\Parameter\Converters\ORM\ORMParameterConverterAnnotationReader',
                'FivePercent\Component\Converter\Parameter\Converters\ORM\ORMParameterConverterReaderInterface',

                'FivePercent\Component\Converter\Converters\ORM\Exception\InvalidArgumentException',
                'FivePercent\Component\Converter\Converters\ORM\Exception\ORMAnnotationNotFoundException',
                'FivePercent\Component\Converter\Converters\ORM\ORMConverter',
                'FivePercent\Component\Converter\Converters\ORM\ORMConverterMetadata'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\Converter\Parameter\Converters\ChainParameterConverter',
            'FivePercent\Component\Converter\Parameter\Converters\SymfonyRequestParameterConverter',

            'FivePercent\Component\Converter\Parameter\ParameterConverterInterface',
            'FivePercent\Component\Converter\Parameter\ParameterConverterManager',
            'FivePercent\Component\Converter\Parameter\ParameterConverterManagerInterface',

            'FivePercent\Component\Converter\Util\KeyGenerator'
        ]);
    }

    /**
     * Register property converter system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerPropertyConverter(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'PropertyConverter',
            'FivePercent\Component\Converter\Property\PropertyConverterInterface',
            'fivepercent/converter'
        );

        $this->propertyConverterEnabled = true;

        $loader->load('converter/property/property_converter.xml');

        if ($config['cache']) {
            $this->registerCachedSystemForService($container, $config['cache'], 'converter.property.datetime.reader');

            $this->addClassesToCompile([
                'FivePercent\Component\Converter\Property\Converters\DateTime\DateTimePropertyConverterCachedReader'
            ]);
        }

        if ($config['orm']) {
            $this->checkLibraryInstalled(
                'ORM Property converter',
                'Doctrine\ORM\Version',
                'doctrine/orm'
            );

            $loader->load('converter/property/orm.xml');

            if ($config['cache']) {
                $this->registerCachedSystemForService($container, $config['cache'], 'converter.property.orm.reader');

                $this->addClassesToCompile([
                    'FivePercent\Component\Converter\Property\Converters\ORM\ORMPropertyConverterCachedReader'
                ]);
            }

            $this->addClassesToCompile([
                'FivePercent\Component\Converter\Converters\ORM\Exception\InvalidArgumentException',
                'FivePercent\Component\Converter\Converters\ORM\Exception\ORMAnnotationNotFoundException',
                'FivePercent\Component\Converter\Converters\ORM\ORMConverter',
                'FivePercent\Component\Converter\Converters\ORM\ORMConverterMetadata',

                'FivePercent\Component\Converter\Property\Converters\ORM\ORMPropertyConverter',
                'FivePercent\Component\Converter\Property\Converters\ORM\ORMPropertyConverterAnnotationReader',
                'FivePercent\Component\Converter\Property\Converters\ORM\ORMPropertyConverterReaderInterface'
            ]);
        }

        if ($config['money']) {
            $this->checkLibraryInstalled(
                'Money',
                'FivePercent\Component\Money\Money',
                'fivepercent/money'
            );

            $loader->load('converter/property/money.xml');

            if ($config['cache']) {
                $this->registerCachedSystemForService($container, $config['cache'], 'converter.property.money.reader');

                $this->addClassesToCompile([
                    'FivePercent\Component\Converter\Property\Converters\Money\MoneyPropertyConverterCachedReader'
                ]);
            }

            $this->addClassesToCompile([
                'FivePercent\Component\Converter\Converters\Money\Exception\MoneyAnnotationNotFoundException',
                'FivePercent\Component\Converter\Converters\Money\MoneyConverter',
                'FivePercent\Component\Converter\Converters\Money\MoneyConverterMetadata',

                'FivePercent\Component\Converter\Property\Converters\Money\MoneyPropertyConverter',
                'FivePercent\Component\Converter\Property\Converters\Money\MoneyPropertyConverterAnnotationReader',
                'FivePercent\Component\Converter\Property\Converters\Money\MoneyPropertyConverterReaderInterface'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\Converter\Converters\DateTime\Exception\DateTimeAnnotationNotFoundException',
            'FivePercent\Component\Converter\Converters\DateTime\DateTimeConverter',
            'FivePercent\Component\Converter\Converters\DateTime\DateTimeConverterMetadata',

            'FivePercent\Component\Converter\Property\PropertyConverterInterface',
            'FivePercent\Component\Converter\Property\PropertyConverterManager',
            'FivePercent\Component\Converter\Property\PropertyConverterManagerInterface',

            'FivePercent\Component\Converter\Property\Converters\ChainPropertyConverter',

            'FivePercent\Component\Converter\Property\Converters\DateTime\DateTimePropertyConverter',
            'FivePercent\Component\Converter\Property\Converters\DateTime\DateTimePropertyConverterAnnotationReader',
            'FivePercent\Component\Converter\Property\Converters\DateTime\DateTimePropertyConverterReaderInterface',

            'FivePercent\Component\Converter\Util\KeyGenerator'
        ]);
    }

    /**
     * Register notifier system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerNotifier(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'Notifier',
            'FivePercent\Component\Notifier\NotifierInterface',
            'fivepercent/notifier'
        );

        $this->notifierEnabled = true;

        $loader->load('notifier/notifier.xml');

        if ($config['cache']) {
            $this->registerCachedSystemForService($container, $config['cache'], 'notifier.metadata_factory');

            $this->addClassesToCompile([
                'FivePercent\Component\Notifier\Metadata\CachedMetadataFactory'
            ]);
        }

        $notifierDefinition = $container->getDefinition('notifier');

        // Register object data extractor
        $objectDataExtractor = $config['object_data_extractor'];

        if ($container->hasDefinition($objectDataExtractor)) {
            $objectDataExtractorDefinition = $container->getDefinition($objectDataExtractor);

            if ($objectDataExtractorDefinition->isAbstract()) {
                $objectDataExtractorDefinition->setAbstract(false);
            }
        }

        $notifierDefinition->replaceArgument(3, new Reference($objectDataExtractor));

        // Register sender
        // @todo: throws exceptions, if many configs
        if (!empty($config['sender']['service'])) {
            $notifierDefinition->replaceArgument(2, new Reference($config['sender']['service']));
        } else if (!empty($config['sender']['amqp'])) {
            $exchangeFactoryDefinition = $container->getDefinition('notifier.sender.amqp_lazy.exchange_factory');
            $exchangeFactoryDefinition->setAbstract(false);
            $exchangeFactoryDefinition->setArguments([
                $config['sender']['amqp']['host'],
                $config['sender']['amqp']['port'],
                $config['sender']['amqp']['vhost'],
                $config['sender']['amqp']['login'],
                $config['sender']['amqp']['password'],
                $config['sender']['amqp']['exchange_name'],
                $config['sender']['amqp']['exchange_type']
            ]);

            $senderDefinition = $container->getDefinition('notifier.sender.amqp_lazy');
            $senderDefinition->setAbstract(false);

            $notifierDefinition->replaceArgument(2, new Reference('notifier.sender.amqp_lazy'));
        } else {
            throw new \RuntimeException('Please configure "notifier.sender" section for register notifier system.');
        }

        if (!empty($config['symfony_event_dispatcher'])) {
            if ($config['symfony_event_dispatcher']['replace']) {
                $this->notifierReplaceSfEventDispatcher = true;
                $this->notifierDisabledSfEventNames = $config['symfony_event_dispatcher']['disabled_event_names'];
            }

            $this->addClassesToCompile([
                'FivePercent\Component\Notifier\EventDispatcher\EventDispatcherNotifierProxy'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\Notifier\Metadata\Loader\AnnotationLoader',
            'FivePercent\Component\Notifier\Metadata\Loader\LoaderInterface',

            'FivePercent\Component\Notifier\Metadata\ClassMetadata',
            'FivePercent\Component\Notifier\Metadata\MetadataFactory',
            'FivePercent\Component\Notifier\Metadata\MetadataFactoryInterface',
            'FivePercent\Component\Notifier\Metadata\NotificationMetadata',

            'FivePercent\Component\Notifier\ObjectData\ObjectDataExtractorInterface',
            'FivePercent\Component\Notifier\ObjectData\ObjectRecreatorInterface',
            'FivePercent\Component\Notifier\ObjectData\Serializable',
            'FivePercent\Component\Notifier\ObjectData\TransformationAndNormalizationExtractor',

            'FivePercent\Component\Notifier\Sender\SenderInterface',

            'FivePercent\Component\Notifier\SenderStrategy\DeferredStrategy',
            'FivePercent\Component\Notifier\SenderStrategy\ImmediatelyStrategy',
            'FivePercent\Component\Notifier\SenderStrategy\SenderStrategyInterface',
            'FivePercent\Component\Notifier\SenderStrategy\SenderStrategyManagerInterface',
            'FivePercent\Component\Notifier\SenderStrategy\StrategyManager',

            'FivePercent\Component\Notifier\Notification',
            'FivePercent\Component\Notifier\Notifier',
            'FivePercent\Component\Notifier\NotifierInterface'
        ]);
    }

    /**
     * Register object security system
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     */
    private function registerObjectSecurity(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'ObjectSecurity',
            'FivePercent\Component\ObjectSecurity\ObjectSecurityAuthorizationCheckerInterface',
            'fivepercent/object-security'
        );

        $this->objectSecurityEnabled = true;

        $loader->load('object_security/object_security.xml');

        if ($config['cache']) {
            $this->registerCachedSystemForService($container, $config['cache'], 'object_security.metadata_factory');

            $this->addClassesToCompile([
                'FivePercent\Component\ObjectSecurity\Metadata\CachedMetadataFactory'
            ]);
        }

        $this->addClassesToCompile([
            'FivePercent\Component\ObjectSecurity\Exception\RuleNotSupportedException',
            'FivePercent\Component\ObjectSecurity\Exception\RuleVotingException',
            'FivePercent\Component\ObjectSecurity\Exception\StrategyNotFoundException',

            'FivePercent\Component\ObjectSecurity\Metadata\Loader\AnnotationLoader',
            'FivePercent\Component\ObjectSecurity\Metadata\Loader\ChainLoader',
            'FivePercent\Component\ObjectSecurity\Metadata\Loader\LoaderInterface',

            'FivePercent\Component\ObjectSecurity\Metadata\Rule\CallbackRule',
            'FivePercent\Component\ObjectSecurity\Metadata\Rule\RoleRule',

            'FivePercent\Component\ObjectSecurity\Metadata\Security\ClassSecurity',
            'FivePercent\Component\ObjectSecurity\Metadata\Security\MethodSecurity',

            'FivePercent\Component\ObjectSecurity\Metadata\MetadataFactory',
            'FivePercent\Component\ObjectSecurity\Metadata\MetadataFactoryInterface',
            'FivePercent\Component\ObjectSecurity\Metadata\Rule',
            'FivePercent\Component\ObjectSecurity\Metadata\Security',

            'FivePercent\Component\ObjectSecurity\Rule\Checker\Strategy\StrategyInterface',
            'FivePercent\Component\ObjectSecurity\Rule\Checker\Strategy\StrategyManagerInterface',
            'FivePercent\Component\ObjectSecurity\Rule\Checker\Strategy\StrategyManager',

            'FivePercent\Component\ObjectSecurity\Rule\Checker\Checker',
            'FivePercent\Component\ObjectSecurity\Rule\Checker\CheckerInterface',

            'FivePercent\Component\ObjectSecurity\Rule\Voter\RuleVoterInterface',

            'FivePercent\Component\ObjectSecurity\ObjectSecurityAuthorizationChecker',
            'FivePercent\Component\ObjectSecurity\ObjectSecurityAuthorizationCheckerInterface'
        ]);
    }

    /**
     * Register transactional system
     *
     * @param array            $config
     * @param XmlFileLoader    $loader
     */
    private function registerTransactional(array $config, XmlFileLoader $loader)
    {
        $this->checkLibraryInstalled(
            'Transactional',
            'FivePercent\Component\Transactional\TransactionalInterface',
            'fivepercent/transactional'
        );

        $this->transactionalEnabled = true;
        $this->transactionalAutoGenerateProxy = $config['proxy_services'];
        $this->transactionalService = $config['service'];

        $loader->load('transactional/transactional.xml');

        $this->addClassesToCompile([
            'FivePercent\Component\Transactional\TransactionalInterface'
        ]);
    }

    /**
     * Check library installed
     *
     * @param string $systemName
     * @param string $interface
     * @param string $libraryName
     *
     * @throws \RuntimeException
     */
    public function checkLibraryInstalled($systemName, $interface, $libraryName)
    {
        if (!interface_exists($interface) && !class_exists($interface)) {
            throw new \RuntimeException(sprintf(
                'Could not register "%s" system in you application, because library '.
                '"%s" not installed. Please add library to your composer.json and run "$ php composer.phar update".',
                $systemName,
                $libraryName
            ));
        }
    }

    /**
     * Register cached system
     *
     * @param ContainerBuilder $container
     * @param string           $cacheServiceId
     * @param string           $serviceId
     * @param string|bool      $cachedServiceId
     */
    protected function registerCachedSystemForService(
        ContainerBuilder $container,
        $cacheServiceId,
        $serviceId,
        $cachedServiceId = true
    ) {
        if ($cachedServiceId === true) {
            $cachedServiceId = $serviceId . '_cached';
        }

        // Step #1: move real service id to "*_delegate"
        $serviceDefinition = $container->getDefinition($serviceId);
        $container->removeDefinition($serviceId);
        $container->setDefinition($serviceId . '_delegate', $serviceDefinition);

        if (!$serviceDefinition->isPublic()) {
            $serviceDefinition->setPublic(false);
        }

        // Step #2: configure cache service
        $cachedServiceDefinition = $container->getDefinition($cachedServiceId);

        if ($cachedServiceDefinition->isAbstract()) {
            $cachedServiceDefinition->setAbstract(false);
        }

        $cachedServiceDefinition->replaceArgument(0, new Reference($serviceId . '_delegate'));
        $cachedServiceDefinition->replaceArgument(1, new Reference($cacheServiceId));

        // Step #3: set alias to cached service
        $container->setAlias($serviceId, $cachedServiceId);
    }
}
