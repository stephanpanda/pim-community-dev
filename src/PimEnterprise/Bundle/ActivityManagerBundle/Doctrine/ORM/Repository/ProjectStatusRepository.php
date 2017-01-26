<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ActivityManager\Factory\ProjectStatusFactoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectStatusRepository extends EntityRepository implements ProjectStatusRepositoryInterface
{
    /** @var SaverInterface */
    protected $projectStatusSaver;

    /** @var ProjectStatusFactoryInterface */
    protected $projectStatusFactory;

    /**
     * @param EntityManager                 $em
     * @param SaverInterface                $projectStatusSaver
     * @param ProjectStatusFactoryInterface $projectStatusFactory
     * @param ClassMetadata                 $class
     */
    public function __construct(
        EntityManager $em,
        SaverInterface $projectStatusSaver,
        ProjectStatusFactoryInterface $projectStatusFactory,
        $class
    ) {
        parent::__construct($em, $em->getClassMetadata($class));

        $this->projectStatusSaver = $projectStatusSaver;
        $this->projectStatusFactory = $projectStatusFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findProjectStatus(ProjectInterface $project, UserInterface $user)
    {
        return $this->findOneBy(['project' => $project, 'user' => $user]);
    }

    /**
     * {@inheritdoc}
     */
    public function wasComplete(ProjectInterface $project, UserInterface $user)
    {
        $projectStatus = $this->findOneBy(['project' => $project, 'user' => $user]);

        if (null === $projectStatus) {
            return false;
        }

        return $projectStatus->isComplete();
    }

    /**
     * {@inheritdoc}
     */
    public function setProjectStatus(ProjectInterface $project, UserInterface $user, $isComplete)
    {
        $projectStatus = $this->findProjectStatus($project, $user);

        if (null === $projectStatus) {
            $projectStatus = $this->projectStatusFactory->create($project, $user);
        }
        $projectStatus->setIsComplete($isComplete);

        $this->projectStatusSaver->save($projectStatus);
    }
}
