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

namespace Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\FamilyCollection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface FamilyRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @param null|string $search
     * @param array $identifiers
     *
     * @return FamilyCollection
     */
    public function findBySearch(int $page, int $limit, ?string $search, array $identifiers): FamilyCollection;
}
