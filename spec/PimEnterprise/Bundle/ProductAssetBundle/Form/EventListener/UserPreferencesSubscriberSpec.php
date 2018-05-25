<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Form\EventListener;

use Pim\Bundle\EnrichBundle\Form\Type\LightEntityType;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Akeneo\Asset\Bundle\Form\EventListener\UserPreferencesSubscriber;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class UserPreferencesSubscriberSpec extends ObjectBehavior
{
    function let(TranslatedLabelsProviderInterface $categoryProvider)
    {
        $this->beConstructedWith($categoryProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserPreferencesSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_form_event()
    {
        $this::getSubscribedEvents()->shouldReturn([
            FormEvents::PRE_SET_DATA => 'addFieldToForm'
        ]);
    }

    function it_add_field_to_the_form_before_mapping_data(FormEvent $event, FormInterface $form)
    {
        $event->getForm()->willReturn($form);
        $form->add('emailNotifications', CheckboxType::class, Argument::type('array'))->shouldBeCalled();
        $form->add('assetDelayReminder', IntegerType::class, Argument::type('array'))->shouldBeCalled();
        $form->add('defaultAssetTree', LightEntityType::class, Argument::type('array'))->shouldBeCalled();

        $this->addFieldToForm($event)->shouldReturn(null);
    }
}
