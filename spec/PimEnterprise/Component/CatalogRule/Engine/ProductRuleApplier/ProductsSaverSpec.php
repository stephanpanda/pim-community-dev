<?php

namespace spec\PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class ProductsSaverSpec extends ObjectBehavior
{
    function let(
        BulkSaverInterface $productSaver,
        VersionManager $versionManager,
        VersionContext $versionContext,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $productSaver,
            $versionManager,
            $versionContext,
            $translator
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier\ProductsSaver');
    }

    function it_saves_products(
        $productSaver,
        $versionManager,
        $versionContext,
        $translator,
        ProductInterface $product,
        RuleInterface $rule
    ) {
        $translator->trans(Argument::cetera())->willReturn('Applied rule "rule_one"');
        $versionManager->isRealTimeVersioning()->willReturn(false);
        $versionContext->addContextInfo('Applied rule "rule_one"', 'default')->shouldBeCalled();
        $versionManager->setRealTimeVersioning(false)->shouldBeCalled();
        $productSaver->saveAll(Argument::any())->shouldBeCalled();
        $versionContext->unsetContextInfo('default')->shouldBeCalled();

        $this->save($rule, [$product]);
    }
}
