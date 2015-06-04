<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\Notifier\Strategy;

use FivePercent\Component\Notifier\SenderStrategy\DeferredStrategy as BaseDeferredStrategy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Override deferred strategy for send events on kernel terminate event
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class DeferredStrategy extends BaseDeferredStrategy implements EventSubscriberInterface
{
    /**
     * On Symfony terminate
     */
    public function onSymfonyKernelTerminate()
    {
        $this->flush();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => [
                ['onSymfonyKernelTerminate', 128]
            ]
        ];
    }
}