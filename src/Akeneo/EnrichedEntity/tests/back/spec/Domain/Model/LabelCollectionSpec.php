<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Model;

use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\LocaleInterface;

class LabelCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromArray',[['en_US' => 'A US label', 'fr_FR' => 'Un label français']]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LabelCollection::class);
    }

    public function it_cannot_create_a_label_collection_if_keys_are_not_string()
    {
        $this->beConstructedThrough('fromArray', [['label1', 'label2']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_create_a_label_collection_if_values_are_an_integer()
    {
        $this->beConstructedThrough('fromArray', [['en_US' => 1]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_create_a_label_collection_if_keys_are_empty()
    {
        $this->beConstructedThrough('fromArray', [['' => 'Book']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_is_constructed_from_an_array_of_labels_and_returns_the_translated_label()
    {
        $this->getLabel('en_US')->shouldReturn('A US label');
        $this->getLabel('fr_FR')->shouldReturn('Un label français');
    }

    public function it_returns_null_if_the_locale_is_not_found()
    {
        $this->getLabel('ru_RU')->shouldReturn(null);
    }

    public function it_tells_if_it_has_label() {
        $this->hasLabel('en_US')->shouldReturn(true);
        $this->hasLabel('ru_RU')->shouldReturn(false);
    }

    public function it_returns_the_locale_codes_it_has_translation_for()
    {
        $this->getLocaleCodes()->shouldReturn(['en_US', 'fr_FR']);
    }
}