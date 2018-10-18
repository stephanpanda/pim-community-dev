<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PublishProductTaskletSpec extends ObjectBehavior
{
    // @todo merge : remove $userManager and $tokenStorage in master branch. They are no longer used.
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        StepExecution $stepExecution,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqbFactory->create(Argument::any())->willReturn($pqb);

        $this->beConstructedWith(
            $manager,
            $paginatorFactory,
            $validator,
            $objectDetacher,
            $userManager,
            $tokenStorage,
            $authorizationChecker,
            $pqbFactory,
            $cacheClearer
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf(StepExecutionAwareInterface::class);
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(2);

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1, $product2])->shouldBeCalled();

        $this->execute();
    }

    function it_executes_a_mass_publish_operation_with_a_configuration_with_invalid_items(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        JobParameters $jobParameters
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);

        $violation1 = new ConstraintViolation('error1', 'spec', [], '', '', $product1);
        $violation2 = new ConstraintViolation('error2', 'spec', [], '', '', $product2);

        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldNotBeCalled(2);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(2);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->addWarning(
            Argument::any(),
            [],
            Argument::type(InvalidItemInterface::class)
        )->shouldBeCalledTimes(4);

        $manager->publishAll([])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(false);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $stepExecution->addWarning(
            Argument::any(),
            [],
            Argument::type(InvalidItemInterface::class)
        )->shouldBeCalled();

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1])->shouldBeCalled();

        $this->execute();
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
