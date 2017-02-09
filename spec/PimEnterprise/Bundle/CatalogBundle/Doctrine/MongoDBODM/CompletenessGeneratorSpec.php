<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class CompletenessGeneratorSpec extends ObjectBehavior
{
    public function let(
        DocumentManager $documentManager,
        ChannelRepositoryInterface $channelRepository,
        FamilyRepositoryInterface $familyRepository,
        AssetRepositoryInterface $assetRepository,
        AttributeRepositoryInterface $attributeRepository,
        EntityManagerInterface $manager
    ) {
        $productClass = 'Pim\Component\Catalog\Model\ProductInterface';

        $this->beConstructedWith(
            $documentManager,
            $channelRepository,
            $familyRepository,
            $assetRepository,
            $attributeRepository,
            $manager,
            $productClass
        );
    }

    public function it_is_an_enterpriseCompletenessGenerator()
    {
        $this->shouldImplement('PimEnterprise\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface');
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\CompletenessGenerator');
    }

    public function it_can_schedule_completeness_for_an_asset(
        $documentManager,
        $attributeRepository,
        Builder $qb,
        Query $query,
        AssetInterface $asset,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $documentManager->createQueryBuilder('Pim\Component\Catalog\Model\ProductInterface')
            ->willReturn($qb);

        $attributeRepository->getAttributeCodesByType('pim_assets_collection')->willReturn(['gallery', 'foobar']);

        $asset->getId()->willReturn(666);

        $qb->update()->willReturn($qb);
        $qb->multiple(true)->willReturn($qb);

        $qb->expr()->willReturn($qb);
        $qb->addOr(\Prophecy\Argument::any())->willReturn($qb);

        $qb->field('normalizedData.gallery')->willReturn($qb);
        $qb->exists(true)->willReturn($qb);
        $qb->field('normalizedData.gallery.id')->willReturn($qb);
        $qb->equals(666)->willReturn($qb);

        $qb->field('normalizedData.foobar')->willReturn($qb);
        $qb->exists(true)->willReturn($qb);
        $qb->field('normalizedData.foobar.id')->willReturn($qb);
        $qb->equals(666)->willReturn($qb);

        $qb->field('completenesses')->willReturn($qb);
        $qb->unsetField()->willReturn($qb);
        $qb->field('normalizedData.completenesses')->willReturn($qb);
        $qb->unsetField()->willReturn($qb);
        $qb->getQuery()->willReturn($query);

        $query->execute()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn(new ArrayCollection());
        $product2->getCompletenesses()->willReturn(new ArrayCollection());
        $this->scheduleForAsset($asset);
    }
}