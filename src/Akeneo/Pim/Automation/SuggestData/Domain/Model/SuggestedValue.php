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

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidSuggestedValueException;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class SuggestedValue
{
    /** @var string */
    private $name;

    /** @var mixed */
    private $value;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
        $this->validate();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Validates that:
     *   - $this->name is a non-empty string
     *   - $this->value is a non-empty string or a non-empty array of strings.
     *
     * @throws InvalidSuggestedValueException
     */
    private function validate(): void
    {
        if ('' === $this->name) {
            throw InvalidSuggestedValueException::emptyName();
        }

        if (is_string($this->value)) {
            if ('' === $this->value) {
                throw InvalidSuggestedValueException::emptyValue();
            }

            return;
        }

        if (is_array($this->value)) {
            if (0 === count($this->value)) {
                throw InvalidSuggestedValueException::emptyValue();
            }
            foreach ($this->value as $value) {
                if (!is_string($value)) {
                    throw InvalidSuggestedValueException::invalidValue();
                }
            }

            return;
        }

        throw InvalidSuggestedValueException::invalidValue();
    }
}
