<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Rule Subscriber
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleSubscriber implements EventSubscriber
{
    /**
     * @var RuleLinkedResourceManager
     */
    protected $linkedResManager;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager
    ) {
        $this->linkedResManager = $linkedResManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preRemove',
        ];
    }

    /**
     * Track preRemove events
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $rule = $args->getEntity();

        if ($rule instanceof RuleInterface) {
            $entityManager = $args->getEntityManager();
            $repository = $entityManager
                ->getRepository('PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource');

            $ruleLinkedResources = $repository
                ->findBy(['rule' => $rule]);

            if (!is_array($ruleLinkedResources)) {
                $ruleLinkedResources = [$ruleLinkedResources];
            }

            foreach ($ruleLinkedResources as $ruleLinkedResource) {
                $this->linkedResManager->remove($ruleLinkedResource);
            }
        }
    }
}
