<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Notification;

use Akeneo\Component\Localization\Presenter\DatePresenter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Component\User\Model\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Notification\ProjectDueDateReminderNotifier;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Notification\ProjectNotificationFactory;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Notification\ProjectNotifierInterface;

class ProjectDueDateReminderNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        DatePresenter $datePresenter
    ) {
        $this->beConstructedWith($projectNotificationFactory, $notifier, $datePresenter, [7, 3, 1]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectDueDateReminderNotifier::class);
    }

    function it_is_a_notifier()
    {
        $this->shouldImplement(ProjectNotifierInterface::class);
    }

    function it_does_not_notify_user_if_the_project_is_complete(
        UserInterface $user,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectCompleteness->isComplete()->willReturn(true);
        $date = new \DateTime();
        $date->add(new \DateInterval('P7D'));
        $project->getDueDate()->willReturn($date);

        $this->notifyUser($user, $project, $projectCompleteness)->shouldReturn(false);
    }

    function it_does_not_notify_user_is_the_the_project_is_complete_but_not_in_the_window_reminder(
        UserInterface $owner,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectCompleteness->isComplete()->willReturn(true);
        $date = new \DateTime();
        $date->add(new \DateInterval('P8D'));
        $project->getDueDate()->willReturn($date);

        $this->notifyUser($owner, $project, $projectCompleteness)->shouldReturn(false);
    }

    function it_notifies_contributors_when_a_due_date_is_close(
        $projectNotificationFactory,
        $notifier,
        $datePresenter,
        NotificationInterface $notification,
        UserInterface $contributor,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        LocaleInterface $locale
    ) {
        $projectCompleteness->isComplete()->willReturn(false);
        $projectCompleteness->getRatioForDone()->willReturn(30);
        $date = new \DateTime();
        $date->add(new \DateInterval('P3D'));
        $project->getDueDate()->willReturn($date);

        $project->getOwner()->willReturn($owner);
        $contributor->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn($date->format('Y-m-d'));
        $project->getLabel()->willReturn('Project label');
        $project->getCode()->willReturn('project-code');

        $contributor->getUsername()->willReturn('boby');
        $owner->getUsername()->willReturn('claude');

        $context = [
            'actionType'  => 'project_due_date',
            'buttonLabel' => 'teamwork_assistant.notification.due_date.start'
        ];

        $parameters = ['%project_label%' => 'Project label', '%due_date%' => $date->format('Y-m-d'), '%percent%' => 30];

        $projectNotificationFactory->create(
            ['identifier' => 'project-code', 'status' => 'contributor-todo'],
            $parameters,
            $context,
            'teamwork_assistant.notification.due_date.contributor'
        )->willReturn($notification);

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project, $projectCompleteness)->shouldReturn(true);
    }

    function it_notifies_owner_when_a_due_date_is_close(
        $projectNotificationFactory,
        $notifier,
        $datePresenter,
        NotificationInterface $notification,
        UserInterface $contributor,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        LocaleInterface $locale
    ) {
        $projectCompleteness->isComplete()->willReturn(false);
        $projectCompleteness->getRatioForDone()->willReturn(30);
        $date = new \DateTime();
        $date->add(new \DateInterval('P3D'));
        $project->getDueDate()->willReturn($date);

        $project->getOwner()->willReturn($owner);
        $contributor->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present($date, ['locale' => 'en_US'])->willReturn($date->format('Y-m-d'));
        $project->getLabel()->willReturn('Project label');
        $project->getCode()->willReturn('project-code');

        $contributor->getUsername()->willReturn('boby');
        $owner->getUsername()->willReturn('boby');

        $context = [
            'actionType'  => 'project_due_date',
            'buttonLabel' => 'teamwork_assistant.notification.due_date.start'
        ];

        $parameters = ['%project_label%' => 'Project label', '%due_date%' => $date->format('Y-m-d'), '%percent%' => 30];

        $projectNotificationFactory->create(
            ['identifier' => 'project-code', 'status' => 'contributor-todo'],
            $parameters,
            $context,
            'teamwork_assistant.notification.due_date.owner'
        )->willReturn($notification);

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project, $projectCompleteness)->shouldReturn(true);
    }
}
