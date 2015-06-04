<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\EnabledChecker;

use Symfony\Component\DependencyInjection\ContainerInterface;
use FivePercent\Component\EnabledChecker\EnabledCheckerInterface;

/**
 * Enabled checker proxy for Symfony2 Container
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class EnabledCheckerProxy implements EnabledCheckerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($object)
    {
        return $this->container->get('enabled_checker.real')->isSupported($object);
    }

    /**
     * {@inheritdoc}
     */
    public function check($object)
    {
        return $this->container->get('enabled_checker.real')->check($object);
    }
}
