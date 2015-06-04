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
use FivePercent\Component\Transactional\Proxy\Generator\ProxyFileGenerator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Check and generate proxy services for transactional layer
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class GenerateProxyTransactionalPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isTransactionalAutoGenerateProxy()) {
            // Transactional system or auto generate proxy is disabled
            return;
        }

        $transactionalId = $this->integrationExtension->getTransactionalService();

        // Validate transactional service
        $transactionalDefinition = $container->getDefinition($transactionalId);
        $class = $transactionalDefinition->getClass();

        try {
            $class = $container->getParameterBag()->resolveValue($class);

            $refClass = new \ReflectionClass($class);
            $requiredInterface = 'FivePercent\Component\Transactional\TransactionalInterface';

            if (!$refClass->implementsInterface($requiredInterface)) {
                throw new \RuntimeException(sprintf(
                    'The transactional service with class "%s" should implement %s.',
                    $class,
                    $requiredInterface
                ));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                'The transactional service with id "%s" is invalid.',
                $transactionalId
            ), 0, $e);
        }

        // Get all services
        $serviceIds = $container->getServiceIds();
        $directory = $container->getParameter('kernel.cache_dir') . '/transactional';

        foreach ($serviceIds as $serviceId) {
            if ($container->hasAlias($serviceId)) {
                // Not check in alias.
                continue;
            }

            $serviceDefinition = $container->getDefinition($serviceId);

            if ($serviceDefinition->isAbstract()) {
                // Not check in abstract service.
                continue;
            }

            $class = $serviceDefinition->getClass();
            $class = $container->getParameterBag()->resolveValue($class);

            if (!$class) {
                continue;
            }

            try {
                $proxyCodeGenerator = new ProxyFileGenerator($directory, $class);
            } catch (\ReflectionException $e) {
                $container->getCompiler()->addLogMessage(sprintf(
                    '%s Error with create proxy code generator for class "%s". Maybe class not found?',
                    get_class($this),
                    $class
                ));

                continue;
            }

            if ($proxyCodeGenerator->needGenerate()) {
                // Generate proxy file
                $filePath = $proxyCodeGenerator->generate();

                $serviceDefinition->setClass($proxyCodeGenerator->getProxyClassName());

                // Add "__setTransactional" method call for set transactional layer
                $methodCalls = $serviceDefinition->getMethodCalls();
                array_unshift($methodCalls, [
                    '___setTransactional',
                    [new Reference($transactionalId)]
                ]);
                $serviceDefinition->setMethodCalls($methodCalls);

                // Add resource for control cache
                $container->addResource(new FileResource($filePath));

                $realClassReflection = new \ReflectionClass($class);
                $container->addResource(new FileResource($realClassReflection->getFileName()));
            }
        }
    }
}
