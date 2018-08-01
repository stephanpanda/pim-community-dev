<?php

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Component\Product\ProductCodeCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ProductSubscriptionRequestSpec extends ObjectBehavior
{
    function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    function it_is_a_product_subscription_request()
    {
        $this->shouldHaveType(ProductSubscriptionRequest::class);
    }

    function it_does_not_take_missing_values_into_account(
        $product,
        IdentifiersMapping $mapping,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        ValueInterface $modelValue,
        ValueInterface $eanValue
    ) {
        $manufacturer->getCode()->willReturn('manufacturer');
        $model->getCode()->willReturn('model');
        $ean->getCode()->willReturn('ean');

        $modelValue->hasData()->willReturn(false);
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789123');

        $product->getValue('manufacturer')->willReturn(null);
        $product->getValue('model')->willReturn($modelValue);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getId()->willReturn(42);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator(
                [
                    'upc'   => $ean->getWrappedObject(),
                    'brand' => $manufacturer->getWrappedObject(),
                    'mpn'   => $model->getWrappedObject(),
                ]
            )
        );

        $this->getMappedValues($mapping)->shouldReturn(
            [
                'upc' => '123456789123',
            ]
        );
    }
}