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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\ConstraintProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveOptionFromAttributeOptionsMapping implements ConstraintCollectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(['fields' => [
            'pim_attribute_code' => new NotBlank(),
            'pim_attribute_option_code' => new NotBlank(),
        ]]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return 'suggest_data_remove_attribute_option_from_mapping' === $job->getName();
    }
}
