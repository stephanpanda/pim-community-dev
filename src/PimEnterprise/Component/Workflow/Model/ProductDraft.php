<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Product draft
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraft implements ProductDraftInterface
{
    /** @var int */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var string */
    protected $author;

    /** @var \DateTime */
    protected $createdAt;

    /** @var ValueCollectionInterface */
    protected $values;

    /** @var array */
    protected $rawValues;

    /** @var array */
    protected $changes = [];

    /** @var int */
    protected $status;

    /** @var array */
    protected $categoryIds = [];

    /** @var string not persisted, used to contextualize the product draft */
    protected $dataLocale = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return (string) $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product): ProductDraftInterface
    {
        $this->product = $product;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor($author): ProductDraftInterface
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt): ProductDraftInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValues(array $rawValues): ProductDraftInterface
    {
        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues(): array
    {
        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setChanges(array $changes): ProductDraftInterface
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangesByStatus($status): array
    {
        $changes = $this->changes;

        if (!isset($changes['values'])) {
            return [];
        }

        foreach ($changes['values'] as $code => $changeset) {
            foreach ($changeset as $index => $change) {
                $changeStatus = $this->getReviewStatusForChange($code, $change['locale'], $change['scope']);
                if ($status !== $changeStatus) {
                    unset($changes['values'][$code][$index]);
                }
            }
        }

        $changes['values'] = array_filter($changes['values']);

        return $changes;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangesToReview(): array
    {
        return $this->getChangesByStatus(self::CHANGE_TO_REVIEW);
    }

    /**
     * {@inheritdoc}
     */
    public function getChange($fieldCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['values'])) {
            return null;
        }

        if (!isset($this->changes['values'][$fieldCode])) {
            return null;
        }

        foreach ($this->changes['values'][$fieldCode] as $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                return $change['data'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChange($fieldCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['values'])) {
            return;
        }

        if (!isset($this->changes['values'][$fieldCode])) {
            return;
        }

        foreach ($this->changes['values'][$fieldCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                unset($this->changes['values'][$fieldCode][$index]);
                $this->removeReviewStatusForChange($fieldCode, $localeCode, $channelCode);
            }
        }

        $this->changes['values'][$fieldCode] = array_values($this->changes['values'][$fieldCode]);

        if (empty($this->changes['values'][$fieldCode])) {
            unset($this->changes['values'][$fieldCode]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReviewStatusForChange($fieldCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['review_statuses'][$fieldCode])) {
            return null;
        }

        foreach ($this->changes['review_statuses'][$fieldCode] as $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                return $change['status'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setReviewStatusForChange($status, $fieldCode, $localeCode, $channelCode)
    {
        if (self::CHANGE_DRAFT !== $status && self::CHANGE_TO_REVIEW !== $status) {
            throw new \LogicException(sprintf('"%s" is not a valid review status', $status));
        }

        if (!isset($this->changes['review_statuses'][$fieldCode])) {
            throw new \LogicException(sprintf('There is no review status for code "%s"', $fieldCode));
        }

        foreach ($this->changes['review_statuses'][$fieldCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                $this->changes['review_statuses'][$fieldCode][$index]['status'] = $status;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setAllReviewStatuses($status)
    {
        if (self::CHANGE_DRAFT !== $status && self::CHANGE_TO_REVIEW !== $status) {
            throw new \LogicException(sprintf('"%s" is not a valid review status', $status));
        }

        $statuses = $this->changes['values'];
        foreach ($statuses as &$items) {
            foreach ($items as &$item) {
                $item['status'] = $status;
                unset($item['data']);
            }
        }

        $this->changes['review_statuses'] = $statuses;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeReviewStatusForChange($fieldCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['review_statuses'][$fieldCode])) {
            return;
        }

        foreach ($this->changes['review_statuses'][$fieldCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                unset($this->changes['review_statuses'][$fieldCode][$index]);
            }
        }

        $this->changes['review_statuses'][$fieldCode] = array_values($this->changes['review_statuses'][$fieldCode]);

        if (empty($this->changes['review_statuses'][$fieldCode])) {
            unset($this->changes['review_statuses'][$fieldCode]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function areAllReviewStatusesTo($status)
    {
        foreach ($this->changes['review_statuses'] as $items) {
            foreach ($items as $item) {
                if ($status !== $item['status']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanges()
    {
        return !empty($this->changes) && !empty($this->changes['values']);
    }

    /**
     * {@inheritdoc}
     */
    public function markAsInProgress()
    {
        $this->status = self::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsReady()
    {
        $this->status = self::READY;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isInProgress()
    {
        return self::IN_PROGRESS === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategoryId($categoryId)
    {
        if (false === $key = array_search($categoryId, $this->categoryIds)) {
            return;
        }

        unset($this->categoryIds[$key]);
        $this->categoryIds = array_values($this->categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataLocale($dataLocale)
    {
        $this->dataLocale = $dataLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataLocale()
    {
        return $this->dataLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->getValues()->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): ValueCollectionInterface
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(ValueCollectionInterface $values): ProductDraftInterface
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addValue(ValueInterface $value)
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeValue(ValueInterface $value)
    {
        $this->values->remove($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes()
    {
        return $this->values->getAttributesKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null)
    {
        return $this->getValues()->getByCodes($attributeCode, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute, $this->getValues()->getAttributes(), true);
    }
}
