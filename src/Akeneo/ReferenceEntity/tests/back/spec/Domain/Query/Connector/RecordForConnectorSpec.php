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

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Query\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Domain\Query\Connector\ValueCollectionForConnector;
use PhpSpec\ObjectBehavior;

class RecordForConnectorSpec extends ObjectBehavior
{
     function let()
    {
        $recordCode = RecordCode::fromString('starck');
        $labelCollection = LabelCollection::fromArray([
            'en_US' => 'Stark',
            'fr_FR' => 'Stark'
        ]);
        $valueCollection = [
            'description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'fr_FR',
                    'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                ],
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                ],
            ],
            'short_description' => [
                [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                ],
            ]
        ];

        $this->beConstructedWith(
            $recordCode,
            $labelCollection,
            Image::createEmpty(),
            $valueCollection
        );
    }

     function it_is_initializable()
    {
        $this->shouldHaveType(RecordForConnector::class);
    }

     function it_normalizes_itself()
     {
         $this->normalize()->shouldReturn([
             'code' => 'starck',
             'labels'                   => [
                 'en_US' => 'Stark',
                 'fr_FR' => 'Stark',
             ],
             'values' => [
                 'description' => [
                     [
                         'channel'   => 'ecommerce',
                         'locale'    => 'fr_FR',
                         'data'      => '.one value per channel ecommerce / one value per locale fr_FR.',
                     ],
                     [
                         'channel'   => 'ecommerce',
                         'locale'    => 'en_US',
                         'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                     ],
                 ],
                 'short_description' => [
                     [
                         'channel'   => 'ecommerce',
                         'locale'    => 'en_US',
                         'data'      => '.one value per channel ecommerce / one value per locale en_US.',
                     ],
                 ]
             ],
             'image' => null,
         ]);
     }
}
