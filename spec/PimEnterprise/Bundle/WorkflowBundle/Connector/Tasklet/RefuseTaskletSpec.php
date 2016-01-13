<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Connector\Tasklet;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes as SecurityAttributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Security\Attributes as WorkflowAttributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class RefuseTaskletSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $productDraftRepository,
        ProductDraftManager $productDraftManager,
        UserProviderInterface $userProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith(
            $productDraftRepository,
            $productDraftManager,
            $userProvider,
            $authorizationChecker,
            $tokenStorage
        );
    }

    function it_refuses_proposals(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft1)->willReturn(true);

        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }

    function it_skips_proposals_if_user_does_not_own_the_product(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(false);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft1)->willReturn(true);

        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }

    function it_skips_proposals_if_user_cannot_edit_the_attributes(
        $productDraftRepository,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft1)->willReturn(false);

        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft2)->willReturn(true);

        $stepExecution->addWarning(Argument::cetera())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(1);
        $this->setStepExecution($stepExecution);

        $this->execute(['draftIds' => [1, 2], 'comment' => null]);
    }

    function it_refuses_proposals_with_a_comment(
        $productDraftRepository,
        $productDraftManager,
        $userProvider,
        $authorizationChecker,
        $tokenStorage,
        UserInterface $userJulia,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');
        $userProvider->loadUserByUsername('julia')->willReturn($userJulia);
        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $productDraftRepository->findByIds(Argument::any())->willReturn([$productDraft1, $productDraft2]);

        $productDraft1->getProduct()->willReturn($product1);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft1)->willReturn(true);

        $productDraft2->getProduct()->willReturn($product2);
        $authorizationChecker->isGranted(SecurityAttributes::OWN, $product2)->willReturn(true);
        $authorizationChecker->isGranted(WorkflowAttributes::FULL_REVIEW, $productDraft2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('refused')->shouldBeCalledTimes(2);
        $this->setStepExecution($stepExecution);

        $productDraftManager->refuse($productDraft1, ['comment' => 'Please fix the typo.'])->shouldBeCalled();
        $productDraftManager->refuse($productDraft2, ['comment' => 'Please fix the typo.'])->shouldBeCalled();

        $this->execute(['draftIds' => [1, 2], 'comment' => 'Please fix the typo.']);
    }
}
