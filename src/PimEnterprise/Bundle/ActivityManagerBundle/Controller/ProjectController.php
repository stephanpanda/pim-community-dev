<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Controller;

use PimEnterprise\Bundle\ActivityManagerBundle\Datagrid\FilterConverter;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\ProjectCalculationJobLauncherInterface;
use PimEnterprise\Component\ActivityManager\Model\DatagridViewTypes;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Project controller.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectController
{
    /** @var FilterConverter */
    private $filterConverter;

    /** @var SimpleFactoryInterface */
    private $datagridViewFactory;

    /** @var SimpleFactoryInterface */
    private $projectFactory;

    /** @var ObjectUpdaterInterface */
    private $datagridViewUpdater;

    /** @var ObjectUpdaterInterface */
    private $projectUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $projectSaver;

    /** @var ProjectCalculationJobLauncherInterface */
    private $projectCalculationJobLauncher;

    /** @var NormalizerInterface */
    private $projectNormalizer;

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        FilterConverter $filterConverter,
        SimpleFactoryInterface $datagridViewFactory,
        SimpleFactoryInterface $projectFactory,
        ObjectUpdaterInterface $datagridViewUpdater,
        ObjectUpdaterInterface $projectUpdater,
        SaverInterface $projectSaver,
        ValidatorInterface $validator,
        ProjectCalculationJobLauncherInterface $projectCalculationJobLauncher, // pas d'interface
        NormalizerInterface $projectNormalizer,
        ProjectRepositoryInterface $projectRepository,
        UserRepositoryInterface $userRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->filterConverter = $filterConverter;
        $this->datagridViewFactory = $datagridViewFactory;
        $this->projectFactory = $projectFactory;
        $this->datagridViewUpdater = $datagridViewUpdater;
        $this->projectUpdater = $projectUpdater;
        $this->validator = $validator;
        $this->projectSaver = $projectSaver;
        $this->projectCalculationJobLauncher = $projectCalculationJobLauncher;
        $this->projectNormalizer = $projectNormalizer;
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $datagridViewFilters = [];
        $projectData = $request->request->get('project');

        parse_str($projectData['datagrid_view']['filters'], $datagridViewFilters);

        $filters = json_encode($datagridViewFilters['f']);
        $filters = $this->filterConverter->convert($request, $filters);

        $projectData['product_filters'] = $filters;
        $projectData['owner'] = $user;
        $projectData['channel'] = $datagridViewFilters['f']['scope']['value'];

        $datagridViewData = [];
        if (isset($projectData['datagrid_view'])) {
            $datagridViewData = $projectData['datagrid_view'];
            $datagridViewData['type'] = DatagridViewTypes::PROJECT_VIEW;
            $datagridViewData['owner'] = $projectData['owner'];
            $datagridViewData['label'] = sprintf('Project %s', time());
            $datagridViewData['datagrid_alias'] = 'product-grid';
        }

        $datagridView = $this->datagridViewFactory->create();
        $this->datagridViewUpdater->update($datagridView, $datagridViewData);

        $projectData['datagrid_view'] = $datagridView;

        $project = $this->projectFactory->create();
        $this->projectUpdater->update($project, $projectData);

        $violations = $this->validator->validate($project);

        if (0 === $violations->count()) {
            $this->projectSaver->save($project);
            $this->projectCalculationJobLauncher->launch($user, $project);

            $normalizedProject = $this->projectNormalizer->normalize($project, 'internal_api');

            return new JsonResponse($normalizedProject, 201);
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * Returns Projects in terms of search and options.
     * Options accept 'limit' => (int) and 'page' => (int) and 'user' => UserInterface.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);

        $projects = $this->projectRepository->findBySearch(
            $request->query->get('search'),
            [
                'limit' => $options['limit'],
                'page' => $options['page'],
                'user' => $this->tokenStorage->getToken()->getUser(),
            ]
        );

        $normalizedProjects = $this->projectNormalizer->normalize($projects, 'internal_api');

        return new JsonResponse($normalizedProjects, 200);
    }

    /**
     * Returns users that belong to the project.
     *
     * @param Request $request
     * @param string  $projectCode
     *
     * @return JsonResponse
     */
    public function searchContributorsAction($projectCode, Request $request)
    {
        $project = $this->projectRepository->findOneByIdentifier($projectCode);

        if (null === $project) {
            return new JsonResponse(null, 404);
        }

        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);

        $users = $this->userRepository->findBySearch(
            $request->query->get('search'),
            [
                'limit' => $options['limit'],
                'page' => $options['page'],
                'project' => $project,
            ]
        );

        $normalizedProjects = $this->projectNormalizer->normalize($users, 'internal_api');

        return new JsonResponse($normalizedProjects, 200);
    }
}
