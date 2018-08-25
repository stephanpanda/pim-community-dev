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

namespace Akeneo\EnrichedEntity\Domain\Query\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextArea;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAttributeDetails extends AbstractAttributeDetails
{
    public const ATTRIBUTE_TYPE = 'text';
    public const MAX_LENGTH = 'max_length';
    public const IS_TEXT_AREA = 'is_text_area';
    public const IS_RICH_TEXT_EDITOR = 'is_rich_text_editor';
    public const VALIDATION_RULE = 'validation_rule';
    public const REGULAR_EXPRESSION = 'regular_expression';

    /** @var AttributeMaxLength */
    public $maxLength;

    /** @var AttributeIsTextArea */
    public $isTextArea;

    /** @var AttributeIsRichTextEditor */
    public $isRichTextEditor;

    /** @var AttributeValidationRule */
    public $validationRule;

    /** @var AttributeRegularExpression */
    public $regularExpression;

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                self::MAX_LENGTH          => $this->maxLength->normalize(),
                self::TYPE                => self::ATTRIBUTE_TYPE,
                self::IS_TEXT_AREA        => $this->isTextArea->normalize(),
                self::IS_RICH_TEXT_EDITOR => $this->isRichTextEditor->normalize(),
                self::VALIDATION_RULE     => $this->validationRule->normalize(),
                self::REGULAR_EXPRESSION  => $this->regularExpression->normalize(),
            ]
        );
    }
}
