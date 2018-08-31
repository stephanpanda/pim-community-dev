<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\EnrichedEntity;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class DeleteActionTest extends ControllerIntegrationTestCase
{
    private const ENRICHED_ENTITY_DELETE_ROUTE = 'akeneo_enriched_entities_enriched_entity_delete_rest';

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
    public function it_deletes_an_enriched_entity_given_an_identifier()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), 204, '');
    }

    /**
     * @test
     */
    public function it_redirects_if_the_request_is_not_an_xml_http_request()
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE'
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_enriched_identifier_is_not_valid()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITY_DELETE_ROUTE,
            ['identifier' => 'des igner'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $expectedResponse = '[{"messageTemplate":"pim_enriched_entity.enriched_entity.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022des igner\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"identifier":"des igner"},"propertyPath":"identifier","invalidValue":"des igner","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]';

        $this->webClientHelper->assertResponse($this->client->getResponse(), 400, $expectedResponse);
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_user_does_not_have_the_acl_to_do_this_action()
    {
        $this->revokeDeletionRights();

        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITY_DELETE_ROUTE,
            ['identifier' => 'designer'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_there_is_no_enriched_entity_with_the_given_identifier()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITY_DELETE_ROUTE,
            ['identifier' => 'unknown'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $this->webClientHelper->assert404NotFound($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_enriched_entity_has_some_records()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::ENRICHED_ENTITY_DELETE_ROUTE,
            ['identifier' => 'brand'],
            'DELETE',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );

        $expectedResponse = '[{"messageTemplate":"pim_enriched_entity.enriched_entity.validation.records.should_have_no_record","parameters":{"%enriched_entity_identifier%":[]},"plural":null,"message":"You cannot delete this entity because records exist for this entity","root":{"identifier":"brand"},"propertyPath":"","invalidValue":{"identifier":"brand"},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]';

        $this->webClientHelper->assertResponse($this->client->getResponse(), 400, $expectedResponse);
    }

    private function getEnrichEntityRepository(): EnrichedEntityRepositoryInterface
    {
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
    }

    private function getRecordRepository(): RecordRepositoryInterface
    {
        return $this->get('akeneo_enrichedentity.infrastructure.persistence.record');
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $enrichedEntityRepository = $this->getEnrichEntityRepository();
        $recordRepository = $this->getRecordRepository();

        $entityItem = EnrichedEntity::create(EnrichedEntityIdentifier::fromString('designer'), [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ]);
        $enrichedEntityRepository->create($entityItem);

        $entityItem = EnrichedEntity::create(EnrichedEntityIdentifier::fromString('brand'), [
            'en_US' => 'Brand',
            'fr_FR' => 'Marque',
        ]);
        $enrichedEntityRepository->create($entityItem);

        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('brand');
        $recordCode = RecordCode::fromString('asus');
        $recordItem = Record::create(
            $recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode),
            $enrichedEntityIdentifier,
            $recordCode,
            [
                'en_US' => 'ASUS',
                'fr_FR' => 'ASUS',
            ]
        );
        $recordRepository->create($recordItem);

        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $fr = new Locale();
        $fr->setId(1);
        $fr->setCode('fr_FR');
        $this->get('pim_catalog.repository.locale')->save($fr);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_enriched_entity_delete', true);
    }

    private function revokeDeletionRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_enriched_entity_delete', false);
    }
}
