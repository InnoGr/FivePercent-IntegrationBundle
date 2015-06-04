<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\ModelTransformer\Annotated;

use Doctrine\Common\Persistence\Proxy;
use FivePercent\Component\ModelTransformer\ContextInterface;
use FivePercent\Component\ModelTransformer\Transformer\Annotated\AnnotatedModelTransformer as BaseAnnotatedModelTransformer;
use FivePercent\Component\ModelTransformer\Transformer\Annotated\Metadata\PropertyMetadata;

/**
 * Override base annotated model transformer for control proxy classes
 *
 * @author Vitaliy Zhuk <zhuk22052gmail.com>
 */
class AnnotatedModelTransformer extends BaseAnnotatedModelTransformer
{
    /**
     * {@inheritDoc}
     */
    public function transform($object, ContextInterface $context)
    {
        if (is_object($object) && $object instanceof Proxy) {
            // Check loaded, and loads if not loaded
            if (!$object->__isInitialized()) {
                $object->__load();
            }
        }

        return parent::transform($object, $context);
    }

    /**
     * {@inheritDoc}
     */
    protected function transformValue($object, $value, PropertyMetadata $metadata, \ReflectionProperty $property)
    {
        if (is_object($value) && $value instanceof Proxy) {
            // Check loaded, and loads if not loaded
            if (!$value->__isInitialized()) {
                $value->__load();
            }
        }

        return parent::transformValue($object, $value, $metadata, $property);
    }
}
