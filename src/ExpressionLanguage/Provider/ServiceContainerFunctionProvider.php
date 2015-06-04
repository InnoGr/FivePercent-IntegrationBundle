<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\ExpressionLanguage\Provider;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Service container provider for expression language
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class ServiceContainerFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'service',
                function ($arg) {
                    return sprintf('$service_container->get(%s)', $arg);
                },
                function (array $variables, $value) {
                    /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
                    $container = $variables['service_container'];

                    return $container->get($value);
                }
            ),

            new ExpressionFunction(
                'parameter',
                function ($arg) {
                    return sprintf('$service_container->getParameter(%s)', $arg);
                },
                function (array $variables, $value) {
                    /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
                    $container = $variables['service_container'];

                    return $container->getParameter($value);
                }
            )
        ];
    }
}
