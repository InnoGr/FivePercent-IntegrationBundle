<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\ModelNormalizer\Annotated;

use Doctrine\Common\Persistence\Proxy;
use FivePercent\Component\ModelNormalizer\ContextInterface;
use FivePercent\Component\ModelNormalizer\Normalizer\Annotated\AnnotatedModelNormalizer as BaseAnnotatedModelNormalizer;
use FivePercent\Component\ModelNormalizer\Normalizer\Annotated\Metadata\PropertyMetadata;

/**
 * Override base annotated model transformer for control proxy classes
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class AnnotatedModelNormalizer extends BaseAnnotatedModelNormalizer
{
    /**
     * {@inheritDoc}
     */
    public function normalize($object, ContextInterface $context)
    {
        if (is_object($object) && $object instanceof Proxy) {
            // Check loaded, and loads if not loaded
            if (!$object->__isInitialized()) {
                $object->__load();
            }
        }

        return parent::normalize($object, $context);
    }

    /**
     * {@inheritDoc}
     */
    protected function normalizeValue($object, $value, PropertyMetadata $metadata, \ReflectionProperty $property)
    {
        if (is_object($value) && $value instanceof Proxy) {
            // Check loaded, and loads if not loaded
            if (!$value->__isInitialized()) {
                $value->__load();
            }
        }

        return parent::normalizeValue($object, $value, $metadata, $property);
    }
}
