<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace App\Test\TestCase\Controller\Model;

use App\Controller\Model\ObjectTypesController;
use BEdita\SDK\BEditaClient;
use BEdita\WebTools\ApiClientProvider;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * {@see \App\Controller\Model\ObjectTypesController} Test Case
 *
 * @coversDefaultClass \App\Controller\Model\ObjectTypesController
 * @uses \App\Controller\Model\ObjectTypesController
 */
class ObjectTypesControllerTest extends TestCase
{
    /**
     * The original API client (not mocked).
     *
     * @var \BEdita\SDK\BEditaClient
     */
    protected $apiClient = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->apiClient = ApiClientProvider::getApiClient();
        $this->loadRoutes();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        ApiClientProvider::setApiClient($this->apiClient);
    }

    /**
     * Test subject
     *
     * @var \App\Controller\Model\ObjectTypesController
     */
    public $ModelController;

    /**
     * Client API
     *
     * @var \BEdita\SDK\BEditaClient
     */
    public $client;

    /**
     * Test default request config
     *
     * @var array
     */
    public $defaultRequestConfig = [
        'environment' => [
            'REQUEST_METHOD' => 'GET',
        ],
        'params' => [
            'resource_type' => 'object_types',
        ],
    ];

    /**
     * Test `save` request config
     *
     * @var array
     */
    public $saveRequestConfig = [
        'environment' => [
            'REQUEST_METHOD' => 'POST',
        ],
        'post' => [
            'prop_name' => 'my_prop',
            'prop_type' => 'datetime',
        ],
        'params' => [
            'resource_type' => 'object_types',
        ],
    ];

    /**
     * Setup api client and auth
     *
     * @return void
     */
    private function setupApi(): void
    {
        $this->client = ApiClientProvider::getApiClient();
        $adminUser = getenv('BEDITA_ADMIN_USR');
        $adminPassword = getenv('BEDITA_ADMIN_PWD');
        $response = $this->client->authenticate($adminUser, $adminPassword);
        $this->client->setupTokens($response['meta']);
    }

    /**
     * Setup controller to test with request config
     *
     * @param array $requestConfig
     * @return void
     */
    protected function setupController(array $requestConfig = []): void
    {
        $config = array_merge($this->defaultRequestConfig, $requestConfig);
        $request = new ServerRequest($config);
        $this->ModelController = new ObjectTypesController($request);
        $this->setupApi();
    }

    /**
     * Test `view` method
     *
     * @covers ::view()
     * @covers ::prepareProperties
     * @return void
     */
    public function testView(): void
    {
        $this->setupController();
        $this->ModelController->view(1);
        $vars = ['resource', 'schema', 'properties'];
        foreach ($vars as $var) {
            static::assertNotEmpty($this->ModelController->viewBuilder()->getVar($var));
        }
        $objectTypeProperties = $this->ModelController->viewBuilder()->getVar('objectTypeProperties');
        static::assertNotEmpty($objectTypeProperties);
        static::assertArrayHasKey('inherited', $objectTypeProperties);
        static::assertArrayHasKey('core', $objectTypeProperties);
        static::assertArrayHasKey('custom', $objectTypeProperties);
    }

    /**
     * Test `view` failure method
     *
     * @covers ::view()
     * @return void
     */
    public function testViewFail(): void
    {
        $this->setupController();
        $result = $this->ModelController->view(0);
        static::assertTrue(($result instanceof Response));
    }

    /**
     * Test `save` method
     *
     * @covers ::save()
     * @covers ::addCustomProperty()
     * @return void
     */
    public function testSave(): void
    {
        $this->saveApiMock();
        $controller = new ObjectTypesController(
            new ServerRequest($this->saveRequestConfig)
        );
        $controller->save();

        $data = $controller->getRequest()->getData();
        static::assertArrayNotHasKey('prop_name', $data);
        static::assertArrayNotHasKey('prop_type', $data);
    }

    /**
     * Test `save` method with empty data
     *
     * @covers ::addCustomProperty()
     * @return void
     */
    public function testSaveEmpty(): void
    {
        $this->saveApiMock();
        $config = $this->saveRequestConfig;
        $config['post'] = ['prop_name' => '', 'prop_type' => ''];
        $controller = new ObjectTypesController(new ServerRequest($config));
        $controller->save();

        $data = $controller->getRequest()->getData();
        static::assertArrayNotHasKey('prop_name', $data);
        static::assertArrayNotHasKey('prop_type', $data);
    }

    /**
     * API client mock for save action
     *
     * @return void
     */
    protected function saveApiMock(): void
    {
        // Setup mock API client.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        $apiClient->method('post')
            ->withAnyParameters()
            ->willReturn(['data' => ['id' => '1']]);

        ApiClientProvider::setApiClient($apiClient);
    }
}
