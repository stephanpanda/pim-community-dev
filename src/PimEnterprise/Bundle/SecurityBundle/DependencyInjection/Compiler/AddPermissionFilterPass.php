<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\DependencyInjection\Compiler;

use PimEnterprise\Bundle\SecurityBundle\Datagrid\Filter\PermissionFilter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AddPermissionFilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $configProdiverDefinition = $container->getDefinition('oro_datagrid.configuration.provider');
        $config = $configProdiverDefinition->getArgument(0);
        $config = array_merge_recursive(
            $config,
            [
                'product-grid' => [
                    'filters' => [
                        'columns' => [
                            'permissions' => [
                                'type' => 'product_permission',
                                'ftype' => 'choice',
                                'data_name' => 'permissions',
                                'label' => 'pimee_workflow.product.permission.label',
                                'options' => [
                                    'field_options' => [
                                        'multiple' => false,
                                        'choices' => [
                                            'pimee_workflow.product.permission.own' => PermissionFilter::OWN,
                                            'pimee_workflow.product.permission.edit' => PermissionFilter::EDIT,
                                            'pimee_workflow.product.permission.view' => PermissionFilter::VIEW,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $configProdiverDefinition->replaceArgument(0, $config);
    }
}
