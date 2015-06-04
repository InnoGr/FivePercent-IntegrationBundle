<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\Util;

/**
 * Symfony helpers
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
final class SymfonyHelper
{
    /**
     * Get bundle name from class
     *
     * @param string $className
     *
     * @return string
     */
    public static function getBundleNameFromClass($className)
    {
        $className = str_replace('\\', '/', $className);

        if (preg_match('/.+\/(.+)Bundle\//', $className, $tmp)) {
            $bundleName = $tmp[1] . 'Bundle';
        } elseif (preg_match('/^Symfony\/Component\/(.+)\/.+/U', $className, $tmp)) {
            $bundleName = 'Symfony '.  $tmp[1] . ' Component';
        } else {
            $bundleName = 'Undefined';
        }

        return $bundleName;
    }
}
