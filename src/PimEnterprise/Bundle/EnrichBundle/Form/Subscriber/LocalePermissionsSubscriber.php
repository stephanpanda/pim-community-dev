<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\LocaleAccessManager;

/**
 * Subscriber to manage permissions on locales
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class LocalePermissionsSubscriber implements EventSubscriberInterface
{
    /**
     * @var LocaleAccessManager
     */
    protected $accessManager;

    /**
     * @param LocaleAccessManager $accessManager
     */
    public function __construct(LocaleAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        );
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function postSetData(FormEvent $event)
    {
        if (null === $event->getData() || null === $event->getData()->getId()) {
            return;
        }

        $form = $event->getForm()->get('permissions');
        $form->get('view')->setData($this->accessManager->getViewUserGroups($event->getData()));
        $form->get('edit')->setData($this->accessManager->getEditUserGroups($event->getData()));
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if (null === $event->getData() || null === $event->getData()->getId()) {
            return;
        }

        $form = $event->getForm();
        if ($form->isValid()) {
            $viewUserGroups = $form->get('permissions')->get('view')->getData();
            $editUserGroups = $form->get('permissions')->get('edit')->getData();
            $this->accessManager->setAccess($event->getData(), $viewUserGroups, $editUserGroups);
        }
    }
}
