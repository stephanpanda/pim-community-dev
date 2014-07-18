<?php

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Event\PropositionEvent;

/**
 * Merge values to keep previous proposition changes that
 * are not sent in the current request (like localized attributes or files)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PreparePropositionChangesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PropositionEvents::PRE_UPDATE => [
                ['keepMedia', 128],
                ['mergeValues', 64],
                ['removeNullValues', 0],
                ['cleanEmptyChangeSet', -128],
                ['sortValues', -128],
            ],
        ];
    }

    /**
     * Keep media changes when from previous proposition
     *
     * @param PropositionEvent $event
     */
    public function keepMedia(PropositionEvent $event)
    {
        $proposition = $event->getProposition();
        $currentChanges = $proposition->getChanges();
        if (!isset($currentChanges['values'])) {
            return;
        }
        $submittedChanges = $event->getChanges();
        foreach ($currentChanges['values'] as $key => $value) {
            if (isset($value['media']) && !isset($submittedChanges['values'][$key])) {
                $submittedChanges['values'][$key] = $value;
            }
        }

        $event->setChanges($submittedChanges);
    }

    /**
     * Merge old and new values
     *
     * @param PropositionEvent $event
     */
    public function mergeValues(PropositionEvent $event)
    {
        $proposition = $event->getProposition();
        $submittedChanges = $event->getChanges();

        $oldValue = array_merge(['values'=>[]], $proposition->getChanges());
        $newValue = array_merge(['values'=>[]], $submittedChanges);

        $event->setChanges(
            [
                'values' => array_merge(
                    $oldValue['values'],
                    $newValue['values']
                )
            ]
        );
    }

    /**
     * Unset removed changes
     *
     * @param PropositionEvent $event
     *
     * @return null
     */
    public function removeNullValues(PropositionEvent $event)
    {
        $submittedChanges = $event->getChanges();
        if (!isset($submittedChanges['values'])) {
            return;
        }

        foreach ($submittedChanges['values'] as $key => $value) {
            if (null === $value) {
                unset($submittedChanges['values'][$key]);
            }
        }

        $event->setChanges($submittedChanges);
    }

    /**
     * Sort values for esthetic purpose
     *
     * @param PropositionEvent $event
     */
    public function sortValues(PropositionEvent $event)
    {
        $submittedChanges = $event->getChanges();
        if (!isset($submittedChanges['values'])) {
            return;
        }

        $values = $submittedChanges['values'];
        ksort($values);
        $submittedChanges['values'] = $values;

        $event->setChanges($submittedChanges);
    }

    /**
     * Clean the whole change set when no value change was submitted
     *
     * @param PropositionEvent $event
     */
    public function cleanEmptyChangeSet(PropositionEvent $event)
    {
        $submittedChanges = $event->getChanges();
        if (!isset($submittedChanges['values'])) {
            return;
        }

        if (empty($submittedChanges['values'])) {
            $event->setChanges([]);
        }
    }
}
