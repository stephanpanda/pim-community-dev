<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

class TextPresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface');
    }

    function it_supports_change_if_it_has_a_text_key()
    {
        $this->supportsChange('pim_catalog_textarea')->shouldBe(true);
    }

    function it_presents_text_change_using_the_injected_renderer(
        RendererInterface $renderer,
        Model\ProductValueInterface $value
    ) {
        $value->getData()->willReturn('bar');
        $renderer->renderOriginalDiff(['bar'], ['foo'])->willReturn('diff between bar and foo');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => 'foo'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_before_rendering_diff(
        RendererInterface $renderer,
        Model\ProductValueInterface $value
    ) {
        $value->getData()->willReturn('<p>foo</p> <p>bar</p>');
        $renderer->renderOriginalDiff(['<p>foo</p>', '<p>bar</p>'], ['<p>foo</p>'])->willReturn('diff between bar and foo');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }

    function it_explodes_text_paragraph_without_space_before_rendering_diff(
        RendererInterface $renderer,
        Model\ProductValueInterface $value
    ) {
        $value->getData()->willReturn('<p>foo</p><p>bar</p>');
        $renderer->renderOriginalDiff(['<p>foo</p>', '<p>bar</p>'], ['<p>foo</p>'])->willReturn('diff between bar and foo');

        $this->setRenderer($renderer);
        $this->presentOriginal($value, ['data' => '<p>foo</p>'])->shouldReturn('diff between bar and foo');
    }
}
