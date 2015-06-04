<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle;

use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddEnabledCheckerPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddModelNormalizerPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddModelTransformerPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddNotifierSenderStrategyPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddParameterConverterPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddPropertyConverterPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddObjectSecurityRuleVoterPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\AddVarTagConstraintFactoryPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\GenerateProxyTransactionalPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\NotifierReplaceEventDispatcherPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\Compiler\VarTagReplaceValidatorPass;
use FivePercent\Bundle\IntegrationBundle\DependencyInjection\IntegrationExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Core bundle for integrate FivePercent packages and libraries to Symfony2 Application
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class FivePercentIntegrationBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        /** @var IntegrationExtension $integrationExtension */
        $integrationExtension = $container->getExtension(IntegrationExtension::ALIAS);

        $container->addCompilerPass(new AddEnabledCheckerPass($integrationExtension));
        $container->addCompilerPass(new AddVarTagConstraintFactoryPass($integrationExtension));
        $container->addCompilerPass(new AddModelTransformerPass($integrationExtension));
        $container->addCompilerPass(new AddModelNormalizerPass($integrationExtension));
        $container->addCompilerPass(new AddParameterConverterPass($integrationExtension));
        $container->addCompilerPass(new AddPropertyConverterPass($integrationExtension));
        $container->addCompilerPass(new AddObjectSecurityRuleVoterPass($integrationExtension));
        $container->addCompilerPass(new AddNotifierSenderStrategyPass($integrationExtension));
        $container->addCompilerPass(new VarTagReplaceValidatorPass($integrationExtension));
        $container->addCompilerPass(new NotifierReplaceEventDispatcherPass($integrationExtension));
        $container->addCompilerPass(new GenerateProxyTransactionalPass($integrationExtension));
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new IntegrationExtension();
        }

        return $this->extension;
    }

    /**
     * {@inheritDoc}
     */
    public function registerCommands(Application $application)
    {
        // All commands registered as service
    }
}
