<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\ReferenceData;

use Acme\Bundle\AppBundle\Entity\Fabric;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\ReferenceDataConfigurationInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class ReferenceDataCollectionPresenterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        $this->beConstructedWith($attributeRepository, $repositoryResolver);
    }

    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf(PresenterInterface::class);
    }

    function it_supports_a_multi_reference_data()
    {
        $this->supportsChange('pim_reference_data_multiselect')->shouldBe(true);
    }

    function it_does_not_support_a_simple_reference_data()
    {
        $this->supportsChange('pim_reference_data_simpleselect')->shouldBe(false);
    }

    function it_presents_reference_data_change_using_the_injected_renderer(
        $repositoryResolver,
        ObjectRepository $repository,
        ReferenceDataConfigurationInterface $configuration,
        RendererInterface $renderer,
        CustomValuePresenterCollection $value,
        CustomValuePresenterCollection $leather,
        CustomValuePresenterCollection $neoprene,
        CustomValuePresenterCollection $kevlar
    ) {
        $leather->__toString()->willReturn('Leather');
        $leather->getReferenceDataName()->willReturn('fabrics');
        $neoprene->__toString()->willReturn('[Neoprene]');
        $neoprene->getReferenceDataName()->willReturn('fabrics');
        $kevlar->__toString()->willReturn('Kevlar');
        $kevlar->getReferenceDataName()->willReturn('fabrics');

        $configuration->getClass()->willReturn(Fabric::class);
        $repository->findBy(['code' => ['Leather', 'Neoprene']])->willReturn([$leather, $kevlar]);
        $repositoryResolver->resolve(null)->willReturn($repository);

        $renderer->renderDiff(['Leather', '[Neoprene]'], ['Leather', 'Kevlar'])->willReturn('diff between two reference data');
        $this->setRenderer($renderer);

        $value->getData()->willReturn([$leather, $neoprene]);
        $value->getAttributeCode()->willReturn('fabric');
        $this->present($value, ['data' => ['Leather', 'Neoprene']])->shouldReturn('diff between two reference data');
    }
}

interface CustomValuePresenterCollection extends ValueInterface
{
    public function getReferenceDataName();
    public function getCode();
    public function getData();
}
