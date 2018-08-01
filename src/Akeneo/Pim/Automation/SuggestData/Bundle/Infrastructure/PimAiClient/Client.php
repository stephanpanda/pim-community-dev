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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\PimAiClient;

use Akeneo\Pim\Automation\SuggestData\Component\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\ConfigurationRepositoryInterface;
use GuzzleHttp\ClientInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Client
{
    private $httpClient;

    private $configurationRepository;

    private $token;

    public function __construct(ClientInterface $httpClient, ConfigurationRepositoryInterface $configurationRepository)
    {
        $this->httpClient = $httpClient;
        $this->configurationRepository = $configurationRepository;
    }

    public function request(string $method, string $uri, array $options = [])
    {
        $options = $options + [
            'headers' => ['Authorization' => $this->getToken()],
        ];

        return $this->httpClient->request($method, $uri, $options);
    }

    private function getToken(): ?string
    {
        if (empty($this->token)) {
            $config = $this->configurationRepository->findOneByCode('pim-ai');
            if ($config instanceof Configuration) {
                $this->token = $config->getToken();
            }
        }

        return $this->token;
    }
}