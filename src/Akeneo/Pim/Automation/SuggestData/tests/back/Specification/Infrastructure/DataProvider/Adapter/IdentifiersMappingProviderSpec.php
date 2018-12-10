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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiWebService;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\IdentifiersMappingProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IdentifiersMappingProviderSpec extends ObjectBehavior
{
    public function let(
        IdentifiersMappingApiWebService $api,
        ConfigurationRepositoryInterface $configurationRepo
    ): void {
        $this->beConstructedWith($api, $configurationRepo);

        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);
    }

    public function it_is_an_identifier_mapping_provider(): void
    {
        $this->shouldHaveType(IdentifiersMappingProvider::class);
        $this->shouldImplement(IdentifiersMappingProviderInterface::class);
    }

    public function it_updates_the_identifiers_mapping($api, IdentifiersMapping $mapping): void
    {
        $api->setToken(Argument::type('string'))->shouldBeCalled();
        $mapping->getIdentifiers()->willReturn([]);

        $api->update(Argument::any())->shouldBeCalled();

        $this->updateIdentifiersMapping($mapping);
    }
}
