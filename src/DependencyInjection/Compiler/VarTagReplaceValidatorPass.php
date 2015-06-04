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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compile validation system
 * Replace Symfony validator service for add methods for use "VarTagValidator" system
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class VarTagReplaceValidatorPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isVarTagReplaceSfValidator()) {
            return;
        }

        // @todo: add control validator API version

        // Rename ID for "validator" service
        $sfValidator = $container->getDefinition('validator');
        $container->setDefinition('validator.symfony', $sfValidator);
        $container->removeDefinition('validator');

        $coreValidator = new Definition('FivePercent\Bundle\IntegrationBundle\VarTagValidator\Validator');
        $coreValidator->setArguments([
            new Reference('validator.symfony'),
            new Reference('validator.var_tag')
        ]);

        $container->setDefinition('validator', $coreValidator);

        $container->getDefinition('validator.var_tag')
            ->replaceArgument(0, new Reference('validator.symfony'));
    }
}
