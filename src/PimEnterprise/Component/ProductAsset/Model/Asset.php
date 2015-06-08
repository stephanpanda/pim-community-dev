<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Pim\Component\Classification\Model\TagInterface;

/**
 * Product asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class Asset implements AssetInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var ArrayCollection of BaseCategoryInterface */
    protected $categories;

    /** @var string */
    protected $description;

    /** @var ArrayCollection of ReferenceInterface */
    protected $references;

    /** @var bool */
    protected $isEnabled;

    /** @var \Datetime */
    protected $endOfUseAt;

    /** @var \Datetime */
    protected $createdAt;

    /** @var \Datetime */
    protected $updatedAt;

    /** @var ArrayCollection of TagInterface */
    protected $tags;

    public function __construct()
    {
        $this->references = new ArrayCollection();
        $this->isEnabled  = true;
        $this->createdAt  = new \Datetime();
        $this->updatedAt  = new \Datetime();
        $this->tags       = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferences(ArrayCollection $references)
    {
        $this->references = $references;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addReference(ReferenceInterface $reference)
    {
        if (!$this->references->contains($reference)) {
            $this->references->add($reference);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeReference(ReferenceInterface $reference)
    {
        if ($this->references->contains($reference)) {
            $this->references->removeElement($reference);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference(LocaleInterface $locale = null)
    {
        if ($this->getReferences()->isEmpty()) {
            return null;
        }

        foreach ($this->getReferences() as $reference) {
            if ($locale === $reference->getLocale()) {
                return $reference;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasReference(LocaleInterface $locale = null)
    {
        return null !== $this->getReference($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getVariations()
    {
        $variations = [];

        foreach ($this->getReferences() as $reference) {
            $variations = array_merge($variations, $reference->getVariations()->toArray());
        }

        return $variations;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariation(ChannelInterface $channel, LocaleInterface $locale = null)
    {
        foreach ($this->getVariations() as $variation) {
            if ($variation->getChannel() === $channel && $variation->getLocale() === $locale) {
                return $variation;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariation(ChannelInterface $channel, LocaleInterface $locale = null)
    {
        return null !== $this->getVariation($channel, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndOfUseAt()
    {
        return $this->endOfUseAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndOfUseAt(\Datetime $endOfUseAt)
    {
        $this->endOfUseAt = $endOfUseAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'code';
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTag(TagInterface $tag)
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addTag(TagInterface $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagCodes()
    {
        $tags = [];
        foreach ($this->getTags() as $tag) {
            $tags[] = $tag->getCode();
        }
        sort($tags);

        return implode(',', $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategory(BaseCategoryInterface $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCategory(BaseCategoryInterface $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryCodes()
    {
        $codes = array();
        foreach ($this->getCategories() as $category) {
            $codes[] = $category->getCode();
        }
        sort($codes);

        return implode(',', $codes);
    }

    /**
     * TODO: check this in the validation instead
     * Assert that:
     *   - The unique reference of an asset is not localized
     *   - All the references of an asset that contains several references are localized
     *
     * @throws \LogicException
     */
    protected function assertValidReferences()
    {
        $nbReferences  = $this->getReferences()->count();
        $nbLocalizable = 0;

        foreach ($this->getReferences() as $reference) {
            if (null !== $reference->getLocale()) {
                $nbLocalizable++;
            }
        }

        if (1 === $nbReferences && 0 !== $nbLocalizable) {
            throw new \LogicException('The unique reference of an asset can not be localized.');
        }

        if ($nbReferences > 1 && $nbReferences !== $nbLocalizable) {
            throw new \LogicException(
                'All references of an asset that contains several references must be localized.'
            );
        }
    }
}
