<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\ModelTransformer\Transformer\Doctrine\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use FivePercent\Component\ModelTransformer\Transformer\AbstractTraversableModelTransformer;

/**
 * Doctrine ORM Persistence model transformer
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class PersistentCollectionModelTransformer extends AbstractTraversableModelTransformer
{
    /**
     * Returns true if it can transform object, otherwise false.
     *
     * @param string $class
     *
     * @return boolean
     */
    public function supportsClass($class)
    {
        return is_a($class, 'Doctrine\ORM\PersistentCollection', true);
    }

    /**
     * {@inheritDoc}
     */
    protected function createCollection($collection)
    {
        return new ArrayCollection();
    }
}
