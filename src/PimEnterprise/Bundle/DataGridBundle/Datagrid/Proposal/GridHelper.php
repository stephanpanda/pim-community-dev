<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for proposal datagrid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class GridHelper
{
    /** @var ProductDraftRepositoryInterface $draftRepository */
    protected $draftRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ProductDraftRepositoryInterface $draftRepository
     * @param AuthorizationCheckerInterface   $authorizationChecker
     */
    public function __construct(
        ProductDraftRepositoryInterface $draftRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->draftRepository      = $draftRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns callback that will disable approve and refuse buttons given permissions on proposal
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if (null !== $this->authorizationChecker &&
                false === $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $record->getRootEntity())
            ) {
                return ['approve' => false, 'refuse' => false];
            }
        };
    }

    /**
     * Returns available proposal author choices (author can be user or job instance)
     *
     * @return array
     */
    public function getAuthorChoices()
    {
        $authors = $this->draftRepository->getDistinctAuthors();
        $choices = array_combine($authors, $authors);

        return $choices;
    }
}
