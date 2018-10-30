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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

/**
 * It structures data that comes from Franklin and that allows to create proposals.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
final class SuggestedData
{
    /** @var array */
    private $values = [];

    /**
     * @param array $values
     */
    public function __construct(?array $values)
    {
        if (null !== $values) {
            $this->values = $values;
        }
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }
}
