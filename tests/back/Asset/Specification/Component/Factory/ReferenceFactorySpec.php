<?php

namespace Specification\Akeneo\Asset\Component\Factory;

use Akeneo\Asset\Component\Factory\ReferenceFactory;
use Akeneo\Asset\Component\Model\Reference;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Asset\Component\Factory\VariationFactory;
use Akeneo\Asset\Component\Model\VariationInterface;

class ReferenceFactorySpec extends ObjectBehavior
{
    const REFERENCE_CLASS = Reference::class;

    function let(ChannelRepositoryInterface $channelRepository, VariationFactory $variationFactory)
    {
        $this->beConstructedWith($channelRepository, $variationFactory, self::REFERENCE_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType(ReferenceFactory::class);
    }

    function it_creates_a_not_localized_reference(
        $channelRepository,
        $variationFactory,
        ChannelInterface $print,
        ChannelInterface $mobile,
        VariationInterface $variationPrint,
        VariationInterface $variationMobile
    ) {
        $channelRepository->getFullChannels()->willReturn([$print, $mobile]);

        $variationFactory->create($print)->willReturn($variationPrint);
        $variationFactory->create($mobile)->willReturn($variationMobile);

        $this->create()->shouldReturnAnInstanceOf(self::REFERENCE_CLASS);
    }

    function it_creates_a_localized_reference(
        $variationFactory,
        LocaleInterface $fr_FR,
        ChannelInterface $print,
        ChannelInterface $mobile,
        VariationInterface $variationPrint,
        VariationInterface $variationMobile
    ) {
        $fr_FR->getChannels()->willReturn([$print, $mobile]);

        $variationFactory->create($print)->willReturn($variationPrint);
        $variationFactory->create($mobile)->willReturn($variationMobile);

        $this->create($fr_FR)->shouldReturnAnInstanceOf(self::REFERENCE_CLASS);
    }
}