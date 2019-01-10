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

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\Attribute\AppendAttributeOption\AppendAttributeOptionCommand;
use Akeneo\ReferenceEntity\Application\Attribute\AppendAttributeOption\AppendAttributeOptionHandler;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class CreateAttributeOptionContext implements Context
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AppendAttributeOptionHandler */
    private $appendAttributeOptionHandler;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        AppendAttributeOptionHandler $appendAttributeOptionHandler
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->appendAttributeOptionHandler = $appendAttributeOptionHandler;
    }

    /**
     * @Given /^an option attribute$/
     */
    public function anOptionAttribute()
    {
        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::fromString('color'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([ 'fr_FR' => 'Nationalite', 'en_US' => 'Nationality']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );
        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('blue'),
                LabelCollection::fromArray([])
            )
        ]);

        $this->attributeRepository->create($optionAttribute);
    }

    /**
     * @When /^the user appends a new option for this attribute$/
     */
    public function theUserAppendsANewOptionForThisAttribute()
    {
        $command = new AppendAttributeOptionCommand();
        $command->referenceEntityIdentifier = 'designer';
        $command->attributeCode = 'color';
        $command->optionCode = 'red';
        $command->labels = ['en_US' => 'Red', 'fr_FR' => 'Rouge'];

        ($this->appendAttributeOptionHandler)($command);
    }

    /**
     * @Then /^the option is added into the option collection of the attribute$/
     */
    public function theOptionIsAddedIntoTheOptionCollectionOfTheAttribute()
    {
        $attribute = $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString('color'));
        Assert::assertTrue($attribute->hasAttributeOption(OptionCode::fromString('red')));

        $option = $attribute->getAttributeOption(OptionCode::fromString('red'));

        $expectedOption = AttributeOption::create(
            OptionCode::fromString('red'),
            LabelCollection::fromArray(['en_US' => 'Red', 'fr_FR' => 'Rouge'])
        );

        Assert::assertEquals($expectedOption, $option);
    }
}
