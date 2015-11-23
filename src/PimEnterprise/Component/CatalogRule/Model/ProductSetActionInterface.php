<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Model;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\FieldImpactActionInterface;

/**
 * Set action interface used in product rules.
 * An set action is used to set data to a product value.
 *
 * For example : set ['socks'] to categories
 * or          : set 'red' to colors
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface ProductSetActionInterface extends ActionInterface, FieldImpactActionInterface
{
    const ACTION_TYPE = 'set';

    /**
     * @return string
     */
    public function getField();

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return mixed
     */
    public function getOptions();

    /**
     * @param string $field
     *
     * @return ProductAddActionInterface
     */
    public function setField($field);

    /**
     * @param mixed $data
     *
     * @return ProductAddActionInterface
     */
    public function setData($data);

    /**
     * @param array $options
     *
     * @return ProductAddActionInterface
     */
    public function setOptions(array $options = []);
}
