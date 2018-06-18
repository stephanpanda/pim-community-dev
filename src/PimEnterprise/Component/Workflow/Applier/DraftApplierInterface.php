<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Applier;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

/**
 * Product draft applier interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface DraftApplierInterface
{
    /**
     * Apply all changes on the product no matter the review statuses
     *
     * @param EntityWithValuesInterface      $entityWithValues
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     */
    public function applyAllChanges(EntityWithValuesInterface $entityWithValues, EntityWithValuesDraftInterface $entityWithValuesDraft);

    /**
     * Apply only changes with the status EntityWithValuesDraftInterface::TO_REVIEW on the product
     *
     * @param EntityWithValuesInterface      $product
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     */
    public function applyToReviewChanges(EntityWithValuesInterface $entityWithValues, EntityWithValuesDraftInterface $entityWithValuesDraft);
}