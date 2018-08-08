<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity;

use Akeneo\Pim\Permission\Component\Model\JobProfileAccessInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\GroupInterface;

/**
 * Job profile access entity
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfileAccess implements JobProfileAccessInterface
{
    /** @var int */
    protected $id;

    /** @var JobInstance */
    protected $jobProfile;

    /** @var GroupInterface */
    protected $userGroup;

    /** @var bool */
    protected $executeJobProfile;

    /** @var bool */
    protected $editJobProfile;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserGroup(GroupInterface $group)
    {
        $this->userGroup = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobProfile()
    {
        return $this->jobProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function setJobProfile(JobInstance $jobProfile)
    {
        $this->jobProfile = $jobProfile;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isExecuteJobProfile()
    {
        return $this->executeJobProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecuteJobProfile($executeJobProfile)
    {
        $this->executeJobProfile = $executeJobProfile;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditJobProfile()
    {
        return $this->editJobProfile;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditJobProfile($editJobProfile)
    {
        $this->editJobProfile = $editJobProfile;

        return $this->editJobProfile;
    }
}