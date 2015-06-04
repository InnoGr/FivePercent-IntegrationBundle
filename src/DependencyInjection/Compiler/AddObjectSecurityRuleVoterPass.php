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
 * Compile security rule voters
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AddObjectSecurityRuleVoterPass implements CompilerPassInterface
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
        if (!$this->integrationExtension->isObjectSecurityEnabled()) {
            if (count($container->findTaggedServiceIds('object_security.rule_voter')) > 0) {
                throw new \RuntimeException('Could not compile security rule voter, because system is not enabled.');
            }

            return;
        }

        $chainVoterDefinition = $container->getDefinition('object_security.rule_voter');

        foreach ($container->findTaggedServiceIds('object_security.rule_voter') as $id => $attributes) {
            $definition = $container->getDefinition($id);

            $class = $definition->getClass();

            try {
                $class = $container->getParameterBag()->resolveValue($class);
                $refClass = new \ReflectionClass($class);
                $requiredInterface = 'FivePercent\Component\ObjectSecurity\Rule\Voter\RuleVoterInterface';

                if (!$refClass->implementsInterface($requiredInterface)) {
                    throw new \RuntimeException(sprintf(
                        'The rule voter must be implemented of "%s" interface.',
                        $requiredInterface
                    ));
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Could not compile security rule voter with service id "%s".',
                    $id
                ), 0, $e);
            }

            $chainVoterDefinition->addMethodCall('addVoter', [ new Reference($id) ]);
        }
    }
}
