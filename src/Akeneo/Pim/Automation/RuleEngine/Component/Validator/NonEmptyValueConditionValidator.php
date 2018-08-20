<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a value is not empty (except if operator is EMPTY or NOT EMPTY)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class NonEmptyValueConditionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($productCondition, Constraint $constraint)
    {
        if (!($productCondition instanceof ProductConditionInterface)) {
            throw new \LogicException(sprintf(
                'Condition of type "%s" can not be validated.',
                gettype($productCondition)
            ));
        }

        $value = $productCondition->getValue();

        if (Operators::IS_EMPTY !== $productCondition->getOperator() &&
            Operators::IS_NOT_EMPTY !== $productCondition->getOperator() &&
            null === $value
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}