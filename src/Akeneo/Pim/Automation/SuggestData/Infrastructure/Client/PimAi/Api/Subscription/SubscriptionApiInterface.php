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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;

/**
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SubscriptionApiInterface
{
    /**
     * @param array $identifiers
     *
     * @return ApiResponse
     */
    public function subscribeProduct(array $identifiers): ApiResponse;

    /**
     * @return ApiResponse
     */
    public function fetchProducts(): ApiResponse;
}
