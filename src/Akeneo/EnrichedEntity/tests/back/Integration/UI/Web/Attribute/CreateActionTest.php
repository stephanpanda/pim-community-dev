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

namespace Akeneo\EnrichedEntity\tests\back\Integration\UI\Web\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\AuthenticatedClientFactory;
use Akeneo\EnrichedEntity\tests\back\Common\Helper\WebClientHelper;
use Akeneo\EnrichedEntity\tests\back\Integration\ControllerIntegrationTestCase;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class CreateActionTest extends ControllerIntegrationTestCase
{
    private const CREATE_ATTRIBUTE_ROUTE = 'akeneo_enriched_entities_attribute_create_rest';

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
    public function it_creates_a_text_attribute(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [
                    'fr_FR' => 'Nom',
                    'en_US' => 'Name',
                ],
                'type'                       => 'text',
                'value_per_channel'          => true,
                'value_per_locale'           => true,
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_creates_an_image_attribute(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'picture',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'picture',
                'labels'                     => [
                    'fr_FR' => 'Image',
                    'en_US' => 'Picture',
                ],
                'type'                       => 'image',
                'value_per_channel'          => true,
                'value_per_locale'           => true,
            ]
        );

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_automatically_increment_the_attribute_order_on_creation(): void
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'type'                       => 'text',
                'value_per_channel'          => true,
                'value_per_locale'           => true,
            ]
        );

        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'description',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'description',
                'labels'                     => [
                    'fr_FR' => 'Intel',
                    'en_US' => 'Intel',
                ],
                'type'                       => 'text',
                'value_per_channel'          => true,
                'value_per_locale'           => true,
            ]
        );

        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');
        $descriptionAttribute = $attributeRepository->getByIdentifier(
            AttributeIdentifier::create('designer', 'description')
        );

        $this->assertEquals(1, $descriptionAttribute->getOrder()->intValue());
    }

    /**
     * @test
     * @dataProvider invalidAttributeTypes
     */
    public function it_returns_an_error_if_the_attribute_type_is_not_provided($invalidAttributeType)
    {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => 'name',
                    'enriched_entity_identifier' => 'designer'
                ],
                'enriched_entity_identifier' => 'designer',
                'code' => 'name',
                'labels'                     => [],
                'type'                       => $invalidAttributeType,
                'required'                   => false,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            '"There was no valid attribute type provided in the request"'
        );
    }

    /**
     * @test
     * @dataProvider invalidIdentifiers
     */
    public function it_returns_an_error_when_the_attribute_identifier_is_not_valid(
        $recordCode,
        $enrichedEntityIdentifier,
        $enrichedEntityIdentifierURL,
        $type,
        string $expectedResponse
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => $enrichedEntityIdentifierURL,
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            [
                'identifier' => [
                    'identifier' => $recordCode,
                    'enriched_entity_identifier' => $enrichedEntityIdentifier
                ],
                'enriched_entity_identifier' => $enrichedEntityIdentifier,
                'code' => $recordCode,
                'labels'                     => [],
                'type'                       => $type,
                'required'                   => false,
                'value_per_channel'          => false,
                'value_per_locale'           => false,
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedResponse
        );
    }

    /**
     * @test
     */
    public function it_returns_an_error_when_the_attribute_identifier_is_not_unique()
    {
        $urlParameters = ['enrichedEntityIdentifier' => 'designer'];
        $headers = ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'];
        $content = [
            'identifier'                 => [
                'identifier'                 => 'name',
                'enriched_entity_identifier' => 'designer'
            ],
            'enriched_entity_identifier' => 'designer',
            'code'                       => 'name',
            'labels'                     => [],
            'type'                       => 'image',
            'value_per_channel'          => false,
            'value_per_locale'           => false,
        ];
        $method = 'POST';

        $this->webClientHelper->callRoute($this->client, self::CREATE_ATTRIBUTE_ROUTE, $urlParameters, $method, $headers,
            $content);
        $this->webClientHelper->callRoute($this->client, self::CREATE_ATTRIBUTE_ROUTE, $urlParameters, $method, $headers,
            $content);

        $expectedContent = '[{"messageTemplate":"pim_enriched_entity.attribute.validation.identifier.should_be_unique","parameters":{"%enriched_entity_identifier%":"designer","%code%":"name"},"plural":null,"message":"Attribute identifier already exists for enriched entity \u0022designer\u0022 and attribute code \u0022name\u0022","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":1,"required":false,"valuePerChannel":false,"valuePerLocale":false},"constraint":{"targets":"class","defaultOption":null,"requiredOptions":[],"payload":null},"cause":null,"code":null}]';

        $this->webClientHelper->assertResponse($this->client->getResponse(), Response::HTTP_BAD_REQUEST, $expectedContent);
    }

    /**
     * @test
     * @dataProvider invalidValuePerChannelValues
     */
    public function it_returns_an_error_if_the_value_per_channel_flag_is_invalid(
        $invalidValuePerChannel,
        string $expectedMessage
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'type'                       => 'image',
                'value_per_channel'          => $invalidValuePerChannel,
                'value_per_locale'           => false,
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedMessage
        );
    }

    /**
     * @test
     * @dataProvider invalidValuePerLocaleValues
     */
    public function it_returns_an_error_if_the_value_per_locale_flag_is_invalid(
        $invalidValuePerLocale,
        string $expectedMessage
    ) {
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'type'                       => 'image',
                'value_per_channel'          => false,
                'value_per_locale'           => $invalidValuePerLocale,
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_BAD_REQUEST,
            $expectedMessage
        );
    }

    /** @test */
    public function it_returns_an_error_when_the_user_do_not_have_the_rights()
    {
        $this->revokeCreationRights();
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'designer',
            ],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ],
            [
                'identifier'                 => [
                    'identifier'                 => 'name',
                    'enriched_entity_identifier' => 'designer',
                ],
                'enriched_entity_identifier' => 'designer',
                'code'                       => 'name',
                'labels'                     => [],
                'type'                       => 'image',
                'value_per_channel'          => false,
                'value_per_locale'           => true,
            ]
        );

        $this->webClientHelper->assert403Forbidden($this->client->getResponse());
    }

    /**
     * @test
     */
    public function it_redirects_if_not_xmlhttp_request(): void
    {
        $this->client->followRedirects(false);
        $this->webClientHelper->callRoute(
            $this->client,
            self::CREATE_ATTRIBUTE_ROUTE,
            [
                'enrichedEntityIdentifier' => 'celine_dion',
            ],
            'POST'
        );
        $response = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    private function loadFixtures(): void
    {
        $user = new User();
        $user->setUsername('julia');
        $this->get('pim_user.repository.user')->save($user);

        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_create', true);
    }

    private function revokeCreationRights(): void
    {
        $securityFacadeStub = $this->get('oro_security.security_facade');
        $securityFacadeStub->setIsGranted('akeneo_enrichedentity_attribute_create', false);
    }

    public function invalidIdentifiers()
    {
        $longIdentifier = str_repeat('a', 256);

        return [
            // Image
            'Attribute identifier is null'                                                                  => [
                null,
                'brand',
                'brand',
                'image',
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":null,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Attribute identifier is an integer'                                                            => [
                1234123,
                'brand',
                'brand',
                'image',
                '[{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":1234123,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":1234123,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Attribute identifier has a dash character'                                                     => [
                'invalid-identifier',
                'brand',
                'brand',
                'image',
                '[{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Attribute identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                'image',
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":"%s","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier,
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier
                ),
            ],

            // Text
            'Attribute identifier is null'                                                                  => [
                null,
                'brand',
                'brand',
                'text',
                '[{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxLength":null,"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":null,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should not be blank.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be blank.","root":{"maxLength":null,"identifier":{"identifier":null,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":null,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Attribute identifier is an integer'                                                            => [
                1234123,
                'brand',
                'brand',
                'text',
                '[{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"maxLength":null,"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":1234123,"enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value should be of type string.","parameters":{"{{ value }}":"1234123","{{ type }}":"string"},"plural":null,"message":"This value should be of type string.","root":{"maxLength":null,"identifier":{"identifier":1234123,"enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":1234123,"labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":1234123,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Attribute identifier has a dash character'                                                     => [
                'invalid-identifier',
                'brand',
                'brand',
                'text',
                '[{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"maxLength":null,"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"pim_enriched_entity.record.validation.identifier.pattern","parameters":{"{{ value }}":"\u0022invalid-identifier\u0022"},"plural":null,"message":"This field may only contain letters, numbers and underscores.","root":{"maxLength":null,"identifier":{"identifier":"invalid-identifier","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"invalid-identifier","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":"invalid-identifier","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Attribute identifier is 256 characters long'                                                   => [
                $longIdentifier,
                'brand',
                'brand',
                'text',
                sprintf(
                    '[{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"maxLength":null,"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"identifier","invalidValue":{"identifier":"%s","enriched_entity_identifier":"brand"},"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null},{"messageTemplate":"This value is too long. It should have 255 characters or less.","parameters":{"{{ value }}":"\u0022%s\u0022","{{ limit }}":255},"plural":null,"message":"This value is too long. It should have 255 characters or less.","root":{"maxLength":null,"identifier":{"identifier":"%s","enriched_entity_identifier":"brand"},"enrichedEntityIdentifier":"brand","code":"%s","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":false},"propertyPath":"code","invalidValue":"%s","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]',
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier,
                    $longIdentifier, $longIdentifier, $longIdentifier, $longIdentifier
                ),
            ],
        ];
    }

    public function invalidValuePerChannelValues()
    {
        return [
            'Value per channel is null' => [
                null,
                '[{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":0,"required":false,"valuePerChannel":null,"valuePerLocale":false},"propertyPath":"valuePerChannel","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Value per channel is not a boolean' => [
                'wrong_boolean_value',
                '[{"messageTemplate":"This value should be of type boolean.","parameters":{"{{ value }}":"\u0022wrong_boolean_value\u0022","{{ type }}":"boolean"},"plural":null,"message":"This value should be of type boolean.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":0,"required":false,"valuePerChannel":"wrong_boolean_value","valuePerLocale":false},"propertyPath":"valuePerChannel","invalidValue":"wrong_boolean_value","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidValuePerLocaleValues()
    {
        return [
            'Value per locale is null' => [
                null,
                '[{"messageTemplate":"This value should not be null.","parameters":{"{{ value }}":"null"},"plural":null,"message":"This value should not be null.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":null},"propertyPath":"valuePerLocale","invalidValue":null,"constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
            'Value per locale is not a boolean' => [
                'wrong_boolean_value',
                '[{"messageTemplate":"This value should be of type boolean.","parameters":{"{{ value }}":"\u0022wrong_boolean_value\u0022","{{ type }}":"boolean"},"plural":null,"message":"This value should be of type boolean.","root":{"maxFileSize":null,"allowedExtensions":[],"identifier":{"identifier":"name","enriched_entity_identifier":"designer"},"enrichedEntityIdentifier":"designer","code":"name","labels":[],"order":0,"required":false,"valuePerChannel":false,"valuePerLocale":"wrong_boolean_value"},"propertyPath":"valuePerLocale","invalidValue":"wrong_boolean_value","constraint":{"defaultOption":null,"requiredOptions":[],"targets":"property","payload":null},"cause":null,"code":null}]'
            ],
        ];
    }

    public function invalidAttributeTypes()
    {
        return [
            'Attribute type is null' => [null],
            'Attribute type is a integer' => [150]
        ];
    }
}