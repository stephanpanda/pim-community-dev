<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify users for project created.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCreatedNotifier implements ProjectNotifierInterface
{
    /** @var ProjectNotificationFactory */
    protected $projectNotificationFactory;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var DatePresenter */
    protected $datePresenter;

    /**
     * @param ProjectNotificationFactory $projectNotificationFactory
     * @param NotifierInterface          $notifier
     * @param DatePresenter              $datePresenter
     */
    public function __construct(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        DatePresenter $datePresenter
    ) {
        $this->projectNotificationFactory = $projectNotificationFactory;
        $this->notifier = $notifier;
        $this->datePresenter = $datePresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(
        UserInterface $user,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness
    ) {
        if (!$projectStatus->hasBeenNotified() && !$projectCompleteness->isComplete()) {
            $userLocale = $user->getUiLocale();
            $formattedDate = $this->datePresenter->present(
                $project->getDueDate(),
                ['locale' => $userLocale->getCode()]
            );

            $context = [
                'actionType'  => 'project_calculation',
                'buttonLabel' => 'activity_manager.notification.project_calculation.start'
            ];

            if ($user->getUsername() !== $project->getOwner()->getUsername()) {
                $notification = $this->projectNotificationFactory->create(
                    ['identifier' => $project->getCode()],
                    ['%project_label%' => $project->getLabel(), '%due_date%' => $formattedDate],
                    $context,
                    'activity_manager.notification.message'
                );

                $this->notifier->notify($notification, [$user]);

                return true;
            }
        }

        return false;
    }
}
