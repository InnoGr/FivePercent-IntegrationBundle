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

/**
 * Attributes fixer
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
trait AttributesFixerTrait
{
    /**
     * Fix attributes
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function fixAttributes(array $attributes)
    {
        $result = $attributes;

        foreach ($attributes as $attr) {
            $result = array_merge($result, $attr);
        }

        return $result;
    }
}
