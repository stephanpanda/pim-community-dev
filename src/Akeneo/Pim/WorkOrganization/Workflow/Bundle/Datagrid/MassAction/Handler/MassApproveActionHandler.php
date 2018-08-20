<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassAction\Handler;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Mass review action handler
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class MassApproveActionHandler implements MassActionHandlerInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CursorFactoryInterface */
    protected $cursorFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, CursorFactoryInterface $cursorFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction): array
    {
        // dispatch pre handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, []);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_APPROVE_PRE_HANDLER, $massActionEvent);

        $datasource = $datagrid->getDatasource();

        $pqb = $datasource->getProductQueryBuilder();
        $cursor = $this->cursorFactory->createCursor($pqb->getQueryBuilder()->getQuery());

        $productDraftIds = [];
        $productModelDraftIds = [];
        foreach ($cursor as $draft) {
            if ($draft instanceof ProductDraft) {
                $productDraftIds[] = $draft->getId();
            }
            if ($draft instanceof ProductModelDraft) {
                $productModelDraftIds[] = $draft->getId();
            }
        }

        $objectIds = ['product_draft_ids' => $productDraftIds, 'product_model_draft_ids' => $productModelDraftIds];

        // dispatch post handler event
        $massActionEvent = new MassActionEvent($datagrid, $massAction, $objectIds);
        $this->eventDispatcher->dispatch(MassActionEvents::MASS_APPROVE_POST_HANDLER, $massActionEvent);

        return $objectIds;
    }
}