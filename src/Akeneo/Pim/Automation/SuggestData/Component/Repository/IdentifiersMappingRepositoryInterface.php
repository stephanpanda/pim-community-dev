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

namespace Akeneo\Pim\Automation\SuggestData\Component\Repository;

use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;

interface IdentifiersMappingRepositoryInterface
{
    /**
     * Save the identifiers mapping
     *
     * @param IdentifiersMapping $identifiersMapping
     */
    public function save(IdentifiersMapping $identifiersMapping): void;

    /**
     * Return the identifiers mapping
     *
     * @return IdentifiersMapping
     */
    public function find(): IdentifiersMapping;
}
