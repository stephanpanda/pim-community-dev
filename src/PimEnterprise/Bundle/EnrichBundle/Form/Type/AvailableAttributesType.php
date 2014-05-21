<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType as PimAvailableAttributesType;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;

/**
 * Override available attributes type to remove attributes where rights are revoked
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AvailableAttributesType extends PimAvailableAttributesType
{
    /** @var AttributeGroupAccessRepository */
    protected $attGroupAccessRepo;

    /**
     * Construct
     *
     * @param string                         $attributeClass
     * @param AttributeRepository            $attributeRepository
     * @param UserContext                    $userContext
     * @param TranslatorInterface            $translator
     * @param AttributeGroupAccessRepository $attGroupAccessRepo
     */
    public function __construct(
        $attributeClass,
        AttributeRepository $repository,
        UserContext $userContext,
        TranslatorInterface $translator,
        AttributeGroupAccessRepository $attGroupAccessRepo
    ) {
        parent::__construct($attributeClass, $repository, $userContext, $translator);

        $this->attGroupAccessRepo = $attGroupAccessRepo;
    }
}
