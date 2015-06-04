<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\EnabledChecker\Checker;

/**
 * Debug checker registry
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class DebugCheckerRegistry
{
    /**
     * @var array
     */
    private $checkers = [];

    /**
     * Add checker
     *
     * @param string  $serviceId
     * @param string  $class
     * @param integer $priority
     */
    public function addChecker($serviceId, $class, $priority)
    {
        $this->checkers[$serviceId] = [
            'id' => $serviceId,
            'class' => $class,
            'priority' => $priority
        ];
    }

    /**
     * Get checkers
     *
     * @return array
     */
    public function getCheckers()
    {
        uasort($this->checkers, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return 0;
            }

            return $a['priority'] > $b['priority'] ? -1 : 1;
        });

        return $this->checkers;
    }
}
