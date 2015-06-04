<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\ExpressionLanguage;

use FivePercent\Bundle\IntegrationBundle\ExpressionLanguage\Provider\ServiceContainerFunctionProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

/**
 * Container aware expression language
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class ContainerAwareExpressionLanguage extends BaseExpressionLanguage
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Construct
     *
     * @param ContainerInterface   $container
     * @param ParserCacheInterface $cache
     * @param array                $providers
     */
    public function __construct(
        ContainerInterface $container,
        ParserCacheInterface $cache = null,
        array $providers = array()
    ) {
        $this->container = $container;

        array_unshift($providers, new ServiceContainerFunctionProvider());

        parent::__construct($cache, $providers);
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($expression, $values = array())
    {
        $values['service_container'] = $this->container;

        return parent::evaluate($expression, $values);
    }
}
