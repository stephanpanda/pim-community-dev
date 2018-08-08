<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

/**
 * Add action is used in product rules.
 * An add action is used to add data to a collection in a product
 *
 * For example : add ['socks', 'sexy_socks'] to categories
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductAddAction implements ProductAddActionInterface
{
    /** @var string */
    protected $field;

    /** @var array */
    protected $items = [];

    /** @var array */
    protected $options = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->field = isset($data['field']) ? $data['field'] : null;
        $this->items = isset($data['items']) ? $data['items'] : [];
        $this->options = [
            'locale' => isset($data['locale']) ? $data['locale'] : null,
            'scope'  => isset($data['scope']) ? $data['scope'] : null
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpactedFields()
    {
        return [$this->getField()];
    }
}