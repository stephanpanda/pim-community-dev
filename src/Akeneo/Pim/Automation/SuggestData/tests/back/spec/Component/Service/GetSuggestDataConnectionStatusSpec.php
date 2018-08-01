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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSuggestDataConnectionStatusSpec extends ObjectBehavior
{
    function let(
        ConfigurationRepositoryInterface $configurationRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->beConstructedWith($configurationRepository, $dataProviderFactory);
    }

    function it_checks_that_a_connection_is_active(DataProviderInterface $dataProvider, $dataProviderFactory, $configurationRepository)
    {
        $configuration = new Configuration('foobar', ['token' => 'bar']);

        $configurationRepository->findOneByCode('foobar')->willReturn($configuration);
        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(true);

        $this->forCode('foobar')->shouldReturn(true);
    }

    function it_checks_that_a_connection_is_inactive(DataProviderInterface $dataProvider, $dataProviderFactory, $configurationRepository)
    {
        $configuration = new Configuration('foobar', ['token' => 'bar']);

        $configurationRepository->findOneByCode('foobar')->willReturn($configuration);
        $dataProviderFactory->create()->willReturn($dataProvider);
        $dataProvider->authenticate('bar')->willReturn(false);

        $this->forCode('foobar')->shouldReturn(false);
    }

    function it_checks_that_a_connection_does_not_exist($configurationRepository)
    {
        $configurationRepository->findOneByCode('foobar')->willReturn(null);

        $this->forCode('foobar')->shouldReturn(false);
    }
}