<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Form\Type;

use Akeneo\Tool\Bundle\FileStorageBundle\Form\Type\FileInfoType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Form type for asset creation
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CreateAssetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'isLocalized',
            SwitchType::class,
            [
                'mapped'      => false,
                'label'       => 'pimee_product_asset.popin.create.is_localized',
                'constraints' => [ new Type(['type' => 'bool']) ],
            ]
        );
        $builder->add('reference_file', FileInfoType::class);
        $builder->add(
            'code',
            TextType::class,
            [
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                    new Regex(
                        [
                            'pattern' => '/^[a-zA-Z0-9_]+$/',
                            'message' => 'Asset code may contain only letters, numbers and underscores.'
                        ]
                    )
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pimee_product_asset_create';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['upload'],
            ]
        );
    }
}