<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UnpublishProductHandlerSpec extends ObjectBehavior
{
    // @todo merge : remove $userManager and $tokenStorage in master branch. They are no longer used.
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($pqb);
        $pqbFactory->create()->willReturn($pqb);

        $this->beConstructedWith(
            $manager,
            $paginatorFactory,
            $validator,
            $objectDetacher,
            $userManager,
            $authorizationChecker,
            $pqbFactory
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $paginatorFactory,
        $manager,
        $cursor,
        $authorizationChecker,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1000', '1001']
                ]
            ],
            'actions' => []
        ];
        $productsPage = [
            [
                $pubProduct1,
                $pubProduct2
            ]
        ];

        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('mass_unpublished')->shouldBeCalledTimes(2);

        $manager->unpublishAll([$pubProduct1, $pubProduct2])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $paginatorFactory,
        $manager,
        $cursor,
        $authorizationChecker,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1000', '1001']
                ]
            ],
            'actions' => []
        ];
        $productsPage = [
            [
                $pubProduct1,
                $pubProduct2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(false);

        $stepExecution->incrementSummaryInfo('mass_unpublished')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $stepExecution->addWarning('unpublish_product_tasklet', Argument::any(), [], $pubProduct2)->shouldBeCalled();

        $manager->unpublishAll([$pubProduct1])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
