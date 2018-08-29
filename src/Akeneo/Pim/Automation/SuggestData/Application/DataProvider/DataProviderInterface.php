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

namespace Akeneo\Pim\Automation\SuggestData\Application\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderInterface
{
    /**
     * @param ProductSubscriptionRequest $request
     *
     * @return ProductSubscriptionResponse
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse;

    /**
     * @param string $token
     *
     * @return bool
     */
    public function authenticate(string $token): bool;

    /**
     * @return ProductSubscriptionsResponse
     */
    public function fetch(): ProductSubscriptionsResponse;
}
