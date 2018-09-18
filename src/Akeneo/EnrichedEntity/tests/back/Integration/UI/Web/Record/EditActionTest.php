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

namespace Akeneo\EnrichedEntity\Integration\UI\Web\Record;

use Akeneo\EnrichedEntity\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class EditActionTest extends ControllerIntegrationTestCase
{
    private const RECORD_EDIT_ROUTE = 'akeneo_enriched_entities_record_edit_rest';

    /** @var Client */
    private $client;

    /** @var WebClientHelper */
    private $webClientHelper;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
        $this->client = (new AuthenticatedClientFactory($this->get('pim_user.repository.user'), $this->testKernel))
            ->logIn('julia');
        $this->webClientHelper = $this->get('akeneoenriched_entity.tests.helper.web_client_helper');
    }

    /**
     * @test
     */
    public function it_edits_a_record_details(): void
    {
        $postContent = [
            'identifier' => 'singer_celine_dion_a1677570-a278-444b-ab46-baa1db199392',
            'code' => 'celine_dion',
            'enriched_entity_identifier' => 'singer',
            'labels'     => [
                'en_US' => 'Celine Dion',
                'fr_FR' => 'Madame Celine Dion',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode' => 'celine_dion',
                'enrichedEntityIdentifier' => 'singer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);

        $repository = $this->getRecordRepository();
        $recordItem = $repository->getByEnrichedEntityAndCode(
            EnrichedEntityIdentifier::fromString($postContent['enriched_entity_identifier']),
            RecordCode::fromString($postContent['code'])
        );

        Assert::assertEquals(array_keys($postContent['labels']), $recordItem->getLabelCodes());
        Assert::assertEquals($postContent['labels']['en_US'], $recordItem->getLabel('en_US'));
        Assert::assertEquals($postContent['labels']['fr_FR'], $recordItem->getLabel('fr_FR'));
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode' => 'celine_dion',
                'enrichedEntityIdentifier' => 'singer',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_errors_if_we_send_a_bad_request()
    {
        $postContent = [
            'identifier' => 'singer_ah!_a1677570-a278-444b-ab46-baa1db199392',
            'code' => 'ah!',
            'enriched_entity_identifier' => 'singer',
            'labels'     => [
                'en_US' => 'Celine Dion',
                'fr_FR' => 'Madame Celine Dion',
            ],
        ];

        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode' => 'celine_dion',
                'enrichedEntityIdentifier' => 'singer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $postContent = [
            'identifier' => 'singer_celine_dion_a1677570-a278-444b-ab46-baa1db199392',
            'code' => 'celine_dion',
            'enriched_entity_identifier' => 'singer',
            'labels'     => [
                'en_US' => 'Celine Dion',
                'fr_FR' => 'Madame Celine Dion',
            ],
        ];
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode' => 'starck',
                'enrichedEntityIdentifier' => 'singer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST, '"The identifier provided in the route and the one given in the body of the request are different"');
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_enriched_entity_identifier_provided_in_the_route_is_different_from_the_body()
    {
        $postContent = [
            'identifier' => 'singer_celine_dion_a1677570-a278-444b-ab46-baa1db199392',
            'code' => 'celine_dion',
            'enriched_entity_identifier' => 'singer',
            'labels'     => [
                'en_US' => 'Celine Dion',
                'fr_FR' => 'Madame Celine Dion',
            ],
        ];
        $this->webClientHelper->callRoute(
            $this->client,
            self::RECORD_EDIT_ROUTE,
            [
                'recordCode' => 'celine_dion',
                'enrichedEntityIdentifier' => 'coco',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            $postContent
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST, '"The identifier provided in the route and the one given in the body of the request are different"');
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.record');
    }

    private function loadFixtures(): void
    {
        $repository = $this->getRecordRepository();

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('singer');
        $recordCode = RecordCode::fromString('celine_dion');
        $entityItem = Record::create(
            RecordIdentifier::fromString('singer_celine_dion_a1677570-a278-444b-ab46-baa1db199392'),
            $enrichedEntityIdentifier,
            $recordCode,
            [
                'en_US' => 'Celine Dion',
                'fr_FR' => 'Celine Dion',
            ],
            ValueCollection::fromValues([])
        );
        $repository->create($entityItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);
    }
}
