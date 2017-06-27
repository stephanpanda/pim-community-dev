<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation\Unpublish;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnpublishTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(Unpublish::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pimee_enrich_mass_unpublish');
    }

    function it_has_view_and_edit_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => Unpublish::class,
            ]
        )->shouldHaveBeenCalled();
    }
}