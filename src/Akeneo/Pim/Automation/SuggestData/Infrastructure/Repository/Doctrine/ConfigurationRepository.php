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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Entity\Config;

/**
 * Doctrine implementation of the configuration repository.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ConfigurationRepository implements ConfigurationRepositoryInterface
{
    private const TOKEN_KEY = 'token';
    private const PIM_AI_CODE = 'pim-ai';
    private const ORO_CONFIG_RECORD_ID = 1;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find(): Configuration
    {
        $oroConfig = $this->findOroConfig();

        $configuration = new Configuration();
        if (null !== $oroConfig) {
            $tokenString = $oroConfig->getOrCreateValue(null, self::TOKEN_KEY)->getValue();
            $configuration->setToken(new Token($tokenString));
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Configuration $configuration): void
    {
        $oroConfig = $this->findOrCreateOroConfig($configuration);

        $this->entityManager->persist($oroConfig);
        $this->entityManager->flush();
    }

    /**
     * Retrieves an oro config entity from database or creates a new one, then
     * updates it.
     *
     * @param Configuration $configuration
     *
     * @return Config
     */
    private function findOrCreateOroConfig(Configuration $configuration): Config
    {
        $oroConfig = $this->findOroConfig();
        if (null === $oroConfig) {
            $oroConfig = new Config();
            $oroConfig->setEntity(self::PIM_AI_CODE);
            $oroConfig->setRecordId(static::ORO_CONFIG_RECORD_ID);
        }
        $this->updateOroConfigValues($oroConfig, $configuration);

        return $oroConfig;
    }

    /**
     * @return null|Config
     */
    private function findOroConfig(): ?Config
    {
        return $this->entityManager->getRepository(Config::class)->findOneBy([
            'scopedEntity' => self::PIM_AI_CODE,
            'recordId' => self::ORO_CONFIG_RECORD_ID,
        ]);
    }

    /**
     * Updates OroConfigValues with the values of our configuration model.
     * If no OroConfigValues exists for a value, a new one will be created and
     * added to the OroConfig entity.
     *
     * @param Config $oroConfig
     * @param Configuration $configuration
     */
    private function updateOroConfigValues(Config $oroConfig, Configuration $configuration): void
    {
        $oroConfigValue = $oroConfig->getOrCreateValue(null, self::TOKEN_KEY);
        $oroConfigValue->setValue((string) $configuration->getToken());
        $oroConfig->getValues()->add($oroConfigValue);
    }
}
