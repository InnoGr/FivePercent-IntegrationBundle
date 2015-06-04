<?php

/**
 * This file is part of the FivePercentIntegrationBundle package
 *
 * (c) InnovationGroup
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FivePercent\Bundle\IntegrationBundle\VarTagValidator;

use FivePercent\Component\VarTagValidator\VarTagValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Override Symfony validator for add var tag validator functions
 *
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
class Validator implements ValidatorInterface, VarTagValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    private $sfValidator;

    /**
     * @var VarTagValidatorInterface
     */
    private $varTagValidator;

    /**
     * Construct
     *
     * @param ValidatorInterface       $sfValidator
     * @param VarTagValidatorInterface $varTagValidator
     */
    public function __construct(ValidatorInterface $sfValidator, VarTagValidatorInterface $varTagValidator)
    {
        $this->sfValidator = $sfValidator;
        $this->varTagValidator = $varTagValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFor($value)
    {
        return $this->sfValidator->getMetadataFor($value);
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetadataFor($value)
    {
        return $this->sfValidator->hasMetadataFor($value);
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, $constraints = null, $groups = null)
    {
        return $this->sfValidator->validate($value, $constraints, $groups);
    }

    /**
     * {@inheritDoc}
     */
    public function validateProperty($object, $propertyName, $groups = null)
    {
        return $this->sfValidator->validateProperty($object, $propertyName, $groups);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePropertyValue($objectOrClass, $propertyName, $value, $groups = null)
    {
        return $this->sfValidator->validatePropertyValue($objectOrClass, $propertyName, $value, $groups);
    }

    /**
     * {@inheritDoc}
     */
    public function startContext()
    {
        return $this->sfValidator->startContext();
    }

    /**
     * {@inheritDoc}
     */
    public function inContext(ExecutionContextInterface $context)
    {
        return $this->sfValidator->inContext($context);
    }

    /**
     * {@inheritDoc}
     */
    public function validateObjectByVarTags($object)
    {
        return $this->varTagValidator->validateObjectByVarTags($object);
    }
}
