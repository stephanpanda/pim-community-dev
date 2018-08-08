<?php

namespace spec\Akeneo\Pim\Automation\SuggestData\Application\Connector\Writer;

use Akeneo\Pim\Automation\SuggestData\Application\Connector\Writer\PushProductsWriter;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use PhpSpec\ObjectBehavior;

class PushProductsWriterSpec extends ObjectBehavior
{
    public function let(DataProviderFactory $dataProviderFactory, DataProviderInterface $dataProvider)
    {
        $dataProviderFactory->create()->willReturn($dataProvider);
        $this->beConstructedWith($dataProviderFactory, 100);
    }

    public function it_is_initializable($dataProviderFactory, DataProviderInterface $dataProvider)
    {
        $this->shouldHaveType(PushProductsWriter::class);
    }

    public function it_is_an_item_writer()
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_writes_a_product($dataProviderFactory, DataProviderInterface $dataProvider)
    {
        $this->write(['identifier' => 'product_blue'])->shouldReturn(null);
    }
}
