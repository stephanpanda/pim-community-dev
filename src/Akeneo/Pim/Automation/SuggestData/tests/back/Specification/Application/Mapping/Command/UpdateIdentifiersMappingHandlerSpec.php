<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class UpdateIdentifiersMappingHandlerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingInterface $identifiersMappingWebService
    ) {
        $this->beConstructedWith($attributeRepository, $identifiersMappingRepository, $identifiersMappingWebService);
    }

    public function it_is_an_update_identifiers_mapping_handler()
    {
        $this->shouldHaveType(UpdateIdentifiersMappingHandler::class);
    }

    public function it_throws_an_exception_if_an_attribute_does_not_exist(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingInterface $identifiersMappingWebService,
        AttributeInterface $model
    ) {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'mpn'   => 'model',
                'upc'   => null,
                'asin'  => null,
                'brand' => 'attributeNotFound',
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $attributeRepository->findOneByIdentifier('attributeNotFound')->willReturn(null);
        $attributeRepository->findOneByIdentifier(null)->shouldNotBeCalled();

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->update(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            InvalidMappingException::attributeNotFound('attributeNotFound', UpdateIdentifiersMappingHandler::class)
        )->during('handle', [$command]);
    }

    public function it_saves_the_identifiers_mapping(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingInterface $identifiersMappingWebService,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $id
    ) {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'brand' => 'manufacturer',
                'mpn'   => 'model',
                'upc'   => 'ean',
                'asin'  => 'id',
            ]
        );

        $attributeRepository->findOneByIdentifier(Argument::any())->shouldBeCalledTimes(4);
        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $attributeRepository->findOneByIdentifier('id')->willReturn($id);

        $manufacturer->getType()->willReturn('pim_catalog_text');
        $model->getType()->willReturn('pim_catalog_text');
        $ean->getType()->willReturn('pim_catalog_text');
        $id->getType()->willReturn('pim_catalog_text');

        $identifiersMapping = new IdentifiersMapping(
            [
                'brand' => $manufacturer->getWrappedObject(),
                'mpn'   => $model->getWrappedObject(),
                'upc'   => $ean->getWrappedObject(),
                'asin'  => $id->getWrappedObject(),
            ]
        );
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();
        $identifiersMappingWebService->update($identifiersMapping)->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_an_exception_with_invalid_attribute_type(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $model,
        $identifiersMappingRepository
    ) {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'mpn'   => 'model',
                'upc'   => null,
                'asin'  => null,
                'brand' => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $model->getType()->willReturn('unknown_attribute_type');
        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_brand_is_saved_without_mpn(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingInterface $identifiersMappingWebService,
        AttributeInterface $manufacturer,
        AttributeInterface $ean
    ) {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'brand' => 'manufacturer',
                'mpn'   => null,
                'upc'   => 'ean',
                'asin'  => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $manufacturer->getType()->willReturn('pim_catalog_text');
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $ean->getType()->willReturn('pim_catalog_text');
        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->update(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }

    public function it_throws_an_exception_when_mpn_is_saved_without_brand(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingInterface $identifiersMappingWebService,
        AttributeInterface $model,
        AttributeInterface $ean,
        $identifiersMappingRepository
    ) {
        $command = new UpdateIdentifiersMappingCommand(
            [
                'brand' => null,
                'mpn'   => 'model',
                'upc'   => 'ean',
                'asin'  => null,
            ]
        );

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $model->getType()->willReturn('pim_catalog_text');
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $ean->getType()->willReturn('pim_catalog_text');
        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();
        $identifiersMappingWebService->update(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(InvalidMappingException::class)->during('handle', [$command]);
    }
}
