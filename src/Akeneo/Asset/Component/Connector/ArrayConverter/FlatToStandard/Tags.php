<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;

/**
 * Tag collection converter
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Tags implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array (from asset file) to standard structured array of tags.
     * This converter returns the set of parsed items.
     *
     * Before:
     * [
     *     'tags' => 'dog,flowers,cities,animal,sunset',
     * ]
     *
     * After:
     * [
     *     ['code' => 'dog'],
     *     ['code' => 'flowers'],
     *     ['code' => 'cities'],
     *     ['code' => 'animal'],
     *     ['code' => 'sunset'],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);

        $convertedItems = [];
        foreach ($item as $field => $data) {
            if (('tags' === $field) && ('' !== $data)) {
                foreach (explode(',', $data) as $tagCode) {
                    $convertedItems[] = ['code' => $tagCode];
                }
            }
        }

        return $convertedItems;
    }

    /**
     * Validate the item to be parsed.
     *
     * @param array $item
     *
     * @throws DataArrayConversionException
     */
    protected function validate(array $item)
    {
        if (!isset($item['tags'])) {
            throw new DataArrayConversionException(
                sprintf(
                    'Field "tags" is expected, provided fields are "%s"',
                    implode(', ', array_keys($item))
                )
            );
        }
    }
}
