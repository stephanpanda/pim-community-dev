<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Present changes on options data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class OptionsPresenter extends AbstractProductValuePresenter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $optionRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $optionRepository
    ) {
        parent::__construct($attributeRepository);

        $this->optionRepository = $optionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $change)
    {
        $change = array_merge($change, ['attribute' => $value->getAttributeCode()]);

        $options = [];

        foreach ($value->getData() as $optionCode) {
            $options[] = $this->optionRepository->findOneByIdentifier(
                $value->getAttributeCode().'.'.$optionCode
            );
        }

        return $this->renderer->renderDiff(
            $this->normalizeData($options),
            $this->normalizeChange($change)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::OPTION_MULTI_SELECT === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];
        foreach ($data as $option) {
            $result[] = (string) $option;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (null === $change['data']) {
            return null;
        }

        $result = [];

        foreach ($change['data'] as $option) {
            $identifier = sprintf('%s.%s', $change['attribute'], $option);
            $result[] = (string) $this->optionRepository->findOneByIdentifier($identifier);
        }

        return $result;
    }
}
