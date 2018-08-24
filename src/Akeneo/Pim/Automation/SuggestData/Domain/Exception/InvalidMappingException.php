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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Exception;

/**
 * Exception thrown when trying to save a not valid mapping.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
final class InvalidMappingException extends \Exception
{
    /** @var string */
    private const IDENTIFIER_MAPPING_CONSTRAINT_KEY = 'akeneo_suggest_data.entity.identifier_mapping.constraint.%s';

    /** @var string */
    private $className;

    /** @var string */
    private $path;

    /** @var array */
    private $messageParams;

    /**
     * @param string          $className
     * @param string          $message
     * @param array           $messageParams
     * @param string|null     $path
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $className,
        string $message = '',
        array $messageParams = [],
        string $path = null,
        int $code = 400,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->className = $className;
        $this->messageParams = $messageParams;
        $this->path = $path;
    }

    /**
     * @param int         $frequency
     * @param string      $attributeCode
     * @param string      $className
     * @param string|null $path
     *
     * @return InvalidMappingException
     */
    public static function duplicateAttributeCode(
        int $frequency,
        string $attributeCode,
        string $className,
        string $path = null
    ) {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'duplicate_attribute_code');
        $messageParams = [
            'attributeCode' => $attributeCode,
            'frequency' => $frequency,
        ];

        return new static($className, $message, $messageParams, $path);
    }

    /**
     * @param array       $expectedIdentifiers
     * @param array       $givenIdentifiers
     * @param string      $className
     * @param string|null $path
     *
     * @return InvalidMappingException
     */
    public static function missingOrInvalidIdentifiersInMapping(
        array $expectedIdentifiers,
        array $givenIdentifiers,
        string $className,
        string $path = null
    ) {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'missing_or_invalid_identifiers');
        $messageParams = [
            'expected' => implode($expectedIdentifiers, ', '),
            'given' => implode($givenIdentifiers, ', '),
        ];

        return new static($className, $message, $messageParams, $path);
    }

    /**
     * @param string      $attributeCode
     * @param string      $className
     * @param string|null $path
     *
     * @return static
     */
    public static function attributeNotFound(
        string $attributeCode,
        string $className,
        string $path = null
    ) {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'attribute_not_found');
        $messageParams = [
            'attributeCode' => $attributeCode,
        ];

        return new static($className, $message, $messageParams, $path);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
