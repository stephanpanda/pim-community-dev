<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\ImportExportBundle\JobEvents;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Job profile listener used to handle permissions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobPermissionsListener implements EventSubscriberInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JobEvents::PRE_EDIT_JOB_PROFILE             => 'checkEditPermission',
            JobEvents::PRE_EXECUTE_JOB_PROFILE          => 'checkExecutePermission',
            JobEvents::PRE_SHOW_JOB_PROFILE             => 'checkExecutePermission',
            JobEvents::PRE_SHOW_JOB_EXECUTION           => 'checkJobExecutionPermission',
            JobEvents::PRE_DOWNLOAD_FILES_JOB_EXECUTION => 'checkJobExecutionPermission',
            JobEvents::PRE_DOWNLOAD_LOG_JOB_EXECUTION   => 'checkJobExecutionPermission'
        ];
    }

    /**
     * Throws an access denied exception if the user can not edit the job profile
     *
     * @param GenericEvent $event
     */
    public function checkEditPermission(GenericEvent $event)
    {
        $this->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $event->getSubject());
    }

    /**
     * Throws an access denied exception if the user can not execute the job profile
     *
     * @param GenericEvent $event
     */
    public function checkExecutePermission(GenericEvent $event)
    {
        $this->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $event->getSubject());
    }

    /**
     * Throws an access denied exception if the user can not execute the job profile
     *
     * @param GenericEvent $event
     */
    public function checkJobExecutionPermission(GenericEvent $event)
    {
        $this->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $event->getSubject()->getJobInstance());
    }

    /**
     * Throws an access denied exception if the user has not the asked permission
     *
     * @param string      $permission
     * @param JobInstance $jobInstance
     *
     * @throws AccessDeniedException
     */
    protected function isGranted($permission, JobInstance $jobInstance)
    {
        if (false === $this->securityContext->isGranted($permission, $jobInstance)) {
            throw new AccessDeniedException();
        }
    }
}
