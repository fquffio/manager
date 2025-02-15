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

namespace App\Test\TestCase\Controller\Component;

use App\Controller\AppController;
use App\Controller\Component\ConfigComponent;
use App\Controller\Component\ModulesComponent;
use App\Core\Exception\UploadException;
use App\Test\TestCase\Controller\AppControllerTest;
use Authentication\AuthenticationServiceInterface;
use Authentication\Controller\Component\AuthenticationComponent;
use Authentication\Identity;
use Authentication\IdentityInterface;
use BEdita\SDK\BEditaClient;
use BEdita\SDK\BEditaClientException;
use BEdita\WebTools\ApiClientProvider;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {@see \App\Controller\Component\ModulesComponent} Test Case
 *
 * @coversDefaultClass \App\Controller\Component\ModulesComponent
 */
class ModulesComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Controller\Component\ModulesComponent
     */
    public $Modules;

    /**
     * Authentication component
     *
     * @var \Authentication\Controller\Component\AuthenticationComponent;
     */
    public $Authentication;

    public $MyModules;

    /**
     * Test api client
     *
     * @var \BEdita\SDK\BEditaClient
     */
    public $client;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $controller = new AppController();
        $registry = $controller->components();
        $registry->load('Authentication.Authentication');
        /** @var \App\Controller\Component\ModulesComponent $modulesComponent */
        $modulesComponent = $registry->load(ModulesComponent::class);
        $this->Modules = $modulesComponent;
        /** @var \Authentication\Controller\Component\AuthenticationComponent $authenticationComponent */
        $authenticationComponent = $registry->load(AuthenticationComponent::class);
        $this->Authentication = $authenticationComponent;
        $this->MyModules = new class ($registry) extends ModulesComponent
        {
            public $meta = [];

            protected function oEmbedMeta(string $url): ?array
            {
                return $this->meta;
            }

            public function objectTypes(?bool $abstract = null): array
            {
                return ['mices', 'elefants', 'cats', 'dogs'];
            }
        };
        $controller->loadComponent('Authentication');
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->Modules);

        // reset client, force new client creation
        ApiClientProvider::setApiClient(null);
        parent::tearDown();
    }

    /**
     * Get mocked AuthenticationService.
     *
     * @return AuthenticationServiceInterface
     */
    protected function getAuthenticationServiceMock(): AuthenticationServiceInterface
    {
        $authenticationService = $this->getMockBuilder(AuthenticationServiceInterface::class)
            ->getMock();
        $authenticationService->method('clearIdentity')
            ->willReturnCallback(function (ServerRequestInterface $request, ResponseInterface $response): array {
                return [
                    'request' => $request->withoutAttribute('identity'),
                    'response' => $response,
                ];
            });
        $authenticationService->method('persistIdentity')
            ->willReturnCallback(function (ServerRequestInterface $request, ResponseInterface $response, IdentityInterface $identity): array {
                return [
                    'request' => $request->withAttribute('identity', $identity),
                    'response' => $response,
                ];
            });

        return $authenticationService;
    }

    /**
     * Data provider for `testGetProject` test case.
     *
     * @return array
     */
    public function getProjectProvider(): array
    {
        return [
            'ok' => [
                [
                    'name' => 'BEdita',
                    'version' => 'v4.0.0-gustavo',
                ],
                [
                    'project' => [
                        'name' => 'BEdita',
                    ],
                    'version' => 'v4.0.0-gustavo',
                ],
            ],
            'empty' => [
                [
                    'name' => '',
                    'version' => '',
                ],
                [],
            ],
            'client exception' => [
                [
                    'name' => '',
                    'version' => '',
                ],
                new BEditaClientException('I am a client exception'),
            ],
            'other exception' => [
                new \RuntimeException('I am some other kind of exception', 999),
                new \RuntimeException('I am some other kind of exception', 999),
            ],
            'config' => [
                [
                    'name' => 'Gustavo',
                    'version' => '4.1.2',
                ],
                [
                    'version' => '4.1.2',
                ],
                [
                    'name' => 'Gustavo',
                ],
            ],
        ];
    }

    /**
     * Test `getProject()` method.
     *
     * @param array|\Exception $expected Expected result.
     * @param array|\Exception $meta Response to `/home` endpoint.
     * @param array $config Project config to set.
     * @return void
     * @dataProvider getProjectProvider()
     * @covers ::getMeta()
     * @covers ::getProject()
     */
    public function testGetProject($expected, $meta, $config = []): void
    {
        // Mock Authentication component
        $this->Modules->getController()->setRequest($this->Modules->getController()->getRequest()->withAttribute('authentication', $this->getAuthenticationServiceMock()));
        $this->Modules->Authentication->setIdentity(new Identity([]));

        Configure::write('Project', $config);
        if ($expected instanceof \Exception) {
            $this->expectException(get_class($expected));
            $this->expectExceptionCode($expected->getCode());
            $this->expectExceptionMessage($expected->getMessage());
        }

        // Setup mock API client.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        if ($meta instanceof \Exception) {
            $apiClient->method('get')
                ->with('/home')
                ->willThrowException($meta);
        } else {
            $apiClient->method('get')
                ->with('/home')
                ->willReturn(compact('meta'));
        }
        ApiClientProvider::setApiClient($apiClient);

        $actual = $this->Modules->getProject();

        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testIsAbstract` test case.
     *
     * @return array
     */
    public function isAbstractProvider(): array
    {
        return [
            'isAbstractTrue' => [
                true,
                'objects',
            ],
            'isAbstractFalse' => [
                false,
                'documents',
            ],
        ];
    }

    /**
     * Test `isAbstract()` method.
     *
     * @param bool $expected expected results from test
     * @param string $data setup data for test, object type
     * @dataProvider isAbstractProvider()
     * @covers ::isAbstract()
     * @return void
     */
    public function testIsAbstract($expected, $data): void
    {
        /** @var \App\Controller\ModulesController $controller */
        $controller = $this->Modules->getController();
        // mock GET /config.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        $apiClient->method('get')
            ->with('/config')
            ->willReturn([]);
        // set $this->Modules->Config->apiClient
        $property = new \ReflectionProperty(ConfigComponent::class, 'apiClient');
        $property->setAccessible(true);
        $property->setValue($controller->Config, $apiClient);
        // Mock Authentication component
        $controller->setRequest($controller->getRequest()->withAttribute('authentication', $this->getAuthenticationServiceMock()));
        $this->Modules->Authentication->setIdentity(new Identity(['id' => 1, 'roles' => ['guest']]));
        $this->Modules->startup();
        $actual = $this->Modules->isAbstract($data);

        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testObjectTypes` test case.
     *
     * @return array
     */
    public function objectTypesProvider(): array
    {
        return [
            'empty' => [
                [],
                null,
            ],
            'abstractList' => [
                [
                    'objects',
                    'media',
                ],
                true,
            ],
            'concreteList' => [
                [
                    'folders',
                    'documents',
                    'events',
                    'news',
                    'links',
                    'locations',
                    'images',
                    'videos',
                    'audio',
                    'files',
                    'users',
                    'profiles',
                    'publications',
                ],
                false,
            ],
        ];
    }

    /**
     * Test `objectTypes()` method.
     *
     * @param array $expected expected results from test
     * @param bool|null $data setup data for test
     * @dataProvider objectTypesProvider()
     * @covers ::objectTypes()
     * @return void
     */
    public function testObjectTypes($expected, $data): void
    {
        /** @var \App\Controller\ModulesController $controller */
        $controller = $this->Modules->getController();
        // mock GET /config.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        $apiClient->method('get')
            ->with('/config')
            ->willReturn([]);
        // set $this->Modules->Config->apiClient
        $property = new \ReflectionProperty(ConfigComponent::class, 'apiClient');
        $property->setAccessible(true);
        $property->setValue($controller->Config, $apiClient);

        // Mock Authentication component
        $controller->setRequest($controller->getRequest()->withAttribute('authentication', $this->getAuthenticationServiceMock()));
        $this->Modules->Authentication->setIdentity(new Identity(['id' => 1, 'roles' => ['guest']]));

        if (!empty($expected)) {
            $this->Modules->startup();
        }
        $actual = $this->Modules->objectTypes($data);
        sort($actual);
        sort($expected);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testGetModules` test case.
     *
     * @return array
     */
    public function getModulesProvider(): array
    {
        return [
            'ok' => [
                [
                    'bedita',
                    'supporto',
                    'gustavo',
                    'trash',
                ],
                [
                    'resources' => [
                        [
                            'name' => 'gustavo',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'supporto',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'bedita',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'trash',
                        ],
                    ],
                ],
                [
                    'bedita' => [],
                    'supporto' => [],
                ],
            ],
            'ok (trash first)' => [
                [
                    'trash',
                    'supporto',
                    'gustavo',
                    'bedita',
                ],
                [
                    'resources' => [
                        [
                            'name' => 'gustavo',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'supporto',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'bedita',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'trash',
                        ],
                    ],
                ],
                [
                    'trash' => [],
                    'supporto' => [],
                ],
            ],
            'ok translations' => [
                [
                    'bedita',
                    'supporto',
                    'gustavo',
                    'translations',
                ],
                [
                    'resources' => [
                        [
                            'name' => 'gustavo',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'supporto',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'bedita',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'translations',
                        ],
                    ],
                ],
                [
                    'bedita' => [],
                    'supporto' => [],
                ],
            ],
            'client exception' => [
                [],
                new BEditaClientException('I am a client exception'),
            ],
            'other exception' => [
                new \RuntimeException('I am some other kind of exception', 999),
                new \RuntimeException('I am some other kind of exception', 999),
            ],
        ];
    }

    /**
     * Test `getModules()` method.
     *
     * @param string[]|\Exception $expected Expected result.
     * @param array|\Exception $meta Response to `/home` endpoint.
     * @param array $modules Modules configuration.
     * @return void
     * @dataProvider getModulesProvider()
     * @covers ::modulesFromMeta()
     * @covers ::getMeta()
     * @covers ::getModules()
     */
    public function testGetModules($expected, $meta, array $modules = []): void
    {
        // Setup mock API client.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        $apiClient->method('get')
            ->with('/config')
            ->willReturn([]);
        // set $this->Modules->Config->apiClient
        $property = new \ReflectionProperty(ConfigComponent::class, 'apiClient');
        $property->setAccessible(true);
        /** @var \App\Controller\ModulesController $appController */
        $appController = $this->Modules->getController();
        $property->setValue($appController->Config, $apiClient);

        // Mock Authentication component
        $appController->setRequest($appController->getRequest()->withAttribute('authentication', $this->getAuthenticationServiceMock()));
        $this->Modules->Authentication->setIdentity(new Identity(['id' => 1, 'roles' => ['guest']]));

        Configure::write('Modules', $modules);

        if ($expected instanceof \Exception) {
            $this->expectException(get_class($expected));
            $this->expectExceptionCode($expected->getCode());
            $this->expectExceptionMessage($expected->getMessage());
        }

        // Setup mock API client.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        if ($meta instanceof \Exception) {
            $apiClient->method('get')
                ->with('/home')
                ->willThrowException($meta);
        } else {
            $apiClient->method('get')
                ->with('/home')
                ->willReturn(compact('meta'));
        }
        ApiClientProvider::setApiClient($apiClient);

        $actual = Hash::extract($this->Modules->getModules(), '{*}.name');

        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testModulesByAccessControl`.
     *
     * @return array
     */
    public function modulesByAccessControlProvider(): array
    {
        return [
            'empty access control' => [
                ['documents' => [], 'events' => [], 'news' => []],
                [],
                [],
                ['documents' => [], 'events' => [], 'news' => []],
            ],
            'no user' => [
                ['documents' => [], 'events' => [], 'news' => []],
                ['guest'],
                [],
                ['documents' => [], 'events' => [], 'news' => []],
            ],
            'empty roles' => [
                ['documents' => [], 'events' => [], 'news' => []],
                ['guest'],
                ['id' => 1, 'roles' => []],
                ['documents' => [], 'events' => [], 'news' => []],
            ],
            'empty hidden, empty readonly' => [
                ['documents' => [], 'events' => [], 'news' => []],
                [
                    'somerole' => [
                        'hidden' => [],
                        'readonly' => [],
                    ],
                ],
                ['id' => 1, 'roles' => ['somerole']],
                ['documents' => [], 'events' => [], 'news' => []],
            ],
            'hidden + readonly' => [
                ['documents' => [], 'events' => [], 'news' => []],
                [
                    'somerole' => [
                        'hidden' => ['documents'],
                        'readonly' => ['events'],
                    ],
                ],
                ['id' => 1, 'roles' => ['somerole']],
                ['events' => ['hints' => ['allow' => []]], 'news' => []],
            ],
            'multi roles' => [
                ['documents' => [], 'events' => [], 'news' => []],
                [
                    'role1' => [
                        'hidden' => ['news'],
                        'readonly' => ['events'],
                    ],
                    'role2' => [
                        'hidden' => ['documents', 'news'],
                        'readonly' => ['events'],
                    ],
                    'role3' => [
                        'hidden' => ['documents', 'news'],
                        'readonly' => ['events'],
                    ],
                ],
                ['id' => 1, 'roles' => ['role1', 'role2', 'role3']],
                ['documents' => [], 'events' => ['hints' => ['allow' => []]]],
            ],
        ];
    }

    /**
     * Test `modulesByAccessControl` method
     *
     * @param array $modules The modules
     * @param array $accessControl The AccessControl config
     * @param array $user The user
     * @param array $expected The expected modules
     * @return void
     * @dataProvider modulesByAccessControlProvider()
     * @cover ::modulesByAccessControl()
     */
    public function testModulesByAccessControl(array $modules, array $accessControl, array $user, array $expected): void
    {
        // Mock Authentication component
        $this->Modules->getController()->setRequest($this->Modules->getController()->getRequest()->withAttribute('authentication', $this->getAuthenticationServiceMock()));

        // set $this->Modules->modules
        $property = new \ReflectionProperty(ModulesComponent::class, 'modules');
        $property->setAccessible(true);
        $property->setValue($this->Modules, $modules);
        // set AccessControl
        Configure::write('AccessControl', $accessControl);
        // call modulesByAccessControl
        $reflectionClass = new \ReflectionClass($this->Modules);
        $method = $reflectionClass->getMethod('modulesByAccessControl');
        $method->setAccessible(true);
        $this->Modules->Authentication->setIdentity(new Identity($user));
        $method->invokeArgs($this->Modules, []);

        // get $this->Modules->modules
        $property = new \ReflectionProperty(ModulesComponent::class, 'modules');
        $property->setAccessible(true);
        $actual = $property->getValue($this->Modules);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testBeforeRender` test case.
     *
     * @return array
     */
    public function startupProvider(): array
    {
        return [
            'without current module' => [
                1,
                [
                    'gustavo',
                    'supporto',
                ],
                null,
                [
                    'name' => 'BEdita',
                    'version' => 'v4.0.0-gustavo',
                ],
                [
                    'resources' => [
                        [
                            'name' => 'supporto',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'gustavo',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                    ],
                    'project' => [
                        'name' => 'BEdita',
                    ],
                    'version' => 'v4.0.0-gustavo',
                ],
                [
                    'gustavo' => [],
                ],
            ],
            'with current module' => [
                1,
                [
                    'gustavo',
                    'supporto',
                ],
                'supporto',
                [
                    'name' => 'BEdita',
                    'version' => 'v4.0.0-gustavo',
                ],
                [
                    'resources' => [
                        [
                            'name' => 'supporto',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                        [
                            'name' => 'gustavo',
                            'hints' => [
                                'object_type' => true,
                            ],
                        ],
                    ],
                    'project' => [
                        'name' => 'BEdita',
                    ],
                    'version' => 'v4.0.0-gustavo',
                ],
                [
                    'gustavo' => [],
                ],
                'supporto',
            ],
            'no user' => [
                null,
                [],
                null,
                [],
                [],
                [],
                null,
            ],
        ];
    }

    /**
     * Test `startup()` method.
     *
     * @param int|null $userId User id.
     * @param string[] $modules Expected module names.
     * @param string|null $currentModule Expected current module name.
     * @param array $project Expected project info.
     * @param array $meta Response to `/home` endpoint.
     * @param string[] $config Modules configuration.
     * @param string|null $currentModuleName Current module.
     * @return void
     * @dataProvider startupProvider()
     * @covers ::startup()
     */
    public function testBeforeRender($userId, $modules, ?string $currentModule, array $project, array $meta, array $config = [], ?string $currentModuleName = null): void
    {
        /** @var \App\Controller\ModulesController $controller */
        $controller = $this->Modules->getController();
        // mock GET /config.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        $apiClient->method('get')
            ->with('/config')
            ->willReturn([]);
        // set $this->Modules->Config->apiClient
        $property = new \ReflectionProperty(ConfigComponent::class, 'apiClient');
        $property->setAccessible(true);
        $property->setValue($controller->Config, $apiClient);

        // Mock Authentication component
        $controller->setRequest($controller->getRequest()->withAttribute('authentication', $this->getAuthenticationServiceMock()));

        Configure::write('Modules', $config);

        if ($userId) {
            $this->Authentication->setIdentity(new Identity(['id' => $userId, 'roles' => ['guest']]));
        } else {
            $this->Authentication->setIdentity(new Identity([]));
        }

        // Setup mock API client.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();
        $apiClient->method('get')
            ->willReturn(compact('meta'));
        ApiClientProvider::setApiClient($apiClient);

        $clearHomeCache = true;
        $this->Modules->setConfig(compact('apiClient', 'currentModuleName', 'clearHomeCache'));
        $this->Modules->startup();

        $viewVars = $controller->viewBuilder()->getVars();
        static::assertArrayHasKey('project', $viewVars);
        static::assertEquals($project, $viewVars['project']);
        static::assertArrayHasKey('modules', $viewVars);
        static::assertSame($modules, Hash::extract($viewVars['modules'], '{*}.name'));
        if ($currentModule !== null) {
            static::assertArrayHasKey('currentModule', $viewVars);
            static::assertSame($currentModule, Hash::get($viewVars['currentModule'], 'name'));
        } else {
            static::assertArrayNotHasKey('currentModule', $viewVars);
        }
    }

    /**
     * Data provider for `testUpload` test case.
     *
     * @return array
     */
    public function uploadProvider(): array
    {
        $filename = sprintf('%s/tests/files/%s', getcwd(), 'test.png');
        $file = new UploadedFile($filename, filesize($filename), 0, $filename);
        $fileErr = new UploadedFile($filename, filesize($filename), 1, $filename);
        $fileEmpty = new UploadedFile($filename, filesize($filename), 4, $filename);

        return [
            'no file' => [
                [
                    'file' => null,
                    'upload_behavior' => 'file',
                ],
                null,
                false,
            ],
            'model-type empty' => [
                [
                    'file' => $file,
                    'upload_behavior' => 'file',
                ],
                new InternalErrorException('Invalid form data: model-type'),
                false,
            ],
            'model-type not a string' => [
                [
                    'file' => $file,
                    'model-type' => 12345,
                    'upload_behavior' => 'file',
                ],
                new InternalErrorException('Invalid form data: model-type'),
                false,
            ],
            'upload ok' => [
                [
                    'file' => $file,
                    'model-type' => 'images',
                    'upload_behavior' => 'file',
                ],
                null,
                true,
            ],
            'generic upload error' => [
                [
                    'file' => $fileErr,
                    'upload_behavior' => 'file',
                    'model-type' => 'images',
                ],
                new UploadException(null, 1), // !UPLOAD_ERR_OK
                true,
            ],
            'save with empty file' => [
                [
                    'file' => $fileEmpty,
                    'upload_behavior' => 'file',
                    'model-type' => 'images',
                ],
                null,
                false,
            ],
            'upload remote url' => [
                [
                    'remote_url' => 'https://www.youtube.com/watch?v=fE50xrnJnR8',
                    'model-type' => 'videos',
                    'upload_behavior' => 'embed',
                ],
                null,
                [
                    'provider' => 'YouTube',
                    'provider_uid' => 'v=fE50xrnJnR8',
                ],
            ],
        ];
    }

    /**
     * Test `upload` method
     *
     * @param array $requestData The request data
     * @param \Exception|null $expectedException The exception expected
     * @param array|bool $uploaded The upload result (boolean or expected requestdata)
     * @return void
     * @covers ::upload()
     * @covers ::removeStream()
     * @covers ::assocStreamToMedia()
     * @covers ::checkRequestForUpload()
     * @dataProvider uploadProvider()
     */
    public function testUpload(array $requestData, $expectedException, $uploaded): void
    {
        // if upload failed, verify exception
        if ($expectedException != null) {
            $this->expectException(get_class($expectedException));
            $this->expectExceptionCode($expectedException->getCode());
            $this->expectExceptionMessage($expectedException->getMessage());
        }

        // get api client (+auth)
        $this->setupApi();

        if ($requestData['upload_behavior'] === 'file') {
            // do component call
            $this->Modules->upload($requestData);
        } else {
            // mock for ModulesComponent
            $controller = new Controller();
            $registry = $controller->components();
            $myModules = new class ($registry) extends ModulesComponent
            {
                public $meta = [];

                protected function oEmbedMeta(string $url): ?array
                {
                    return $this->meta;
                }

                public function objectTypes(?bool $abstract = null): array
                {
                    return ['mices', 'elefants', 'cats', 'dogs'];
                }
            };
            $myModules->meta = $uploaded;

            $myModules->upload($requestData);
            $result = array_intersect_key($requestData, (array)$uploaded);
            static::assertEquals($uploaded, $result);

            return;
        }

        // if upload ok, verify ID is not null
        if ($uploaded) {
            static::assertArrayHasKey('id', $requestData);

            // test upload of another file to change stream
            $filename = sprintf('%s/tests/files/%s', getcwd(), 'test2.png');
            $file = new UploadedFile($filename, filesize($filename), 0, $filename);
            $requestData = [
                'file' => $file,
                'model-type' => 'images',
                'id' => $requestData['id'],
                'upload_behavior' => 'file',
            ];
            $this->Modules->upload($requestData);
            static::assertArrayHasKey('id', $requestData);
        } else {
            static::assertFalse(isset($requestData['id']));
        }
    }

    /**
     * Test `upload` method for InternalErrorException 'Invalid form data: file.name'
     *
     * @return void
     * @covers ::upload()
     * @covers ::checkRequestForUpload()
     */
    public function testUploadInvalidFormDataFileName(): void
    {
        $expectedException = new InternalErrorException('Invalid form data: file.name');
        $this->expectException(get_class($expectedException));
        $this->expectExceptionCode($expectedException->getCode());
        $this->expectExceptionMessage($expectedException->getMessage());
        $filename = sprintf('%s/tests/files/%s', getcwd(), 'test2.png');
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$filename, filesize($filename), 0, $filename])
            ->getMock();
        $uploadedFile->method('getClientFileName')
            ->willReturn(null);
        $requestData = [
            'file' => $uploadedFile,
            'model-type' => 'images',
            'upload_behavior' => 'file',
        ];
        $this->Modules->upload($requestData);
    }

    /**
     * Test `upload` method for InternalErrorException 'Invalid form data: file.tmp_name'
     *
     * @return void
     * @covers ::upload()
     * @covers ::checkRequestForUpload()
     */
    public function testUploadInvalidFormDataFileTmpName(): void
    {
        $expectedException = new InternalErrorException('Invalid form data: file.tmp_name');
        $this->expectException(get_class($expectedException));
        $this->expectExceptionCode($expectedException->getCode());
        $this->expectExceptionMessage($expectedException->getMessage());
        $filename = sprintf('%s/tests/files/%s', getcwd(), 'test2.png');
        $stream = $this->getMockBuilder(Stream::class)
            ->setConstructorArgs([$filename])
            ->getMock();
        $stream->method('getMetadata')
            ->with('uri')
            ->willReturn(null);
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([$filename, filesize($filename), 0, $filename])
            ->getMock();
        $uploadedFile->method('getClientFileName')
            ->willReturn($filename);
        $uploadedFile->method('getStream')
            ->willReturn($stream);
        $requestData = [
            'file' => $uploadedFile,
            'model-type' => 'images',
            'upload_behavior' => 'file',
        ];
        $this->Modules->upload($requestData);
    }

    /**
     * Test `removeStream` method
     *
     * @return void
     * @covers ::removeStream()
     */
    public function testRemoveStreamWhenThereIsNoStream(): void
    {
        $mockId = '99';
        $requestData = [
            'id' => $mockId,
            'model-type' => 'images',
        ];

        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://api.example.org'])
            ->getMock();

        $apiClient->method('get')
            ->with(sprintf('/images/%s/streams', $mockId))
            ->willReturn([]);

        ApiClientProvider::setApiClient($apiClient);
        $actual = $this->Modules->removeStream($requestData);
        static::assertFalse($actual);
    }

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
     * Test `setDataFromFailedSave`.
     *
     * @covers ::setDataFromFailedSave()
     * @return void
     */
    public function testSetDataFromFailedSave(): void
    {
        // empty case
        $this->Modules->setDataFromFailedSave('', ['id' => 123]);
        $actual = $this->Modules->getController()->getRequest()->getSession()->read('failedSave.123');
        static::assertEmpty($actual);

        // data and expected
        $expected = [ 'id' => 999, 'name' => 'gustavo' ];
        $type = 'documents';

        $this->Modules->setDataFromFailedSave($type, $expected);

        // verify data
        $key = sprintf('failedSave.%s.%s', $type, $expected['id']);
        $actual = $this->Modules->getController()->getRequest()->getSession()->read($key);
        unset($expected['id']);
        static::assertEquals($expected, $actual);
    }

    /**
     * Test `updateFromFailedSave` method.
     *
     * @return void
     * @covers ::setupAttributes()
     * @covers ::updateFromFailedSave()
     */
    public function testUpdateFromFailedSave(): void
    {
        // empty case
        $this->Modules->setDataFromFailedSave('', ['id' => 123]); // wrong data, missing type
        $object = ['id' => 123, 'type' => 'documents'];
        $this->Modules->setupAttributes($object);
        static::assertArrayNotHasKey('attributes', $object);

        // write to session data, to simulate recover from session.
        $object = [
            'id' => 999,
            'type' => 'documents',
            'attributes' => [
                'name' => 'john doe',
            ],
        ];
        $recover = [ 'name' => 'gustavo' ];
        $this->Modules->setDataFromFailedSave('documents', $recover + ['id' => 999]);

        // verify data
        $this->Modules->setupAttributes($object);
        $expected = $object;
        $expected['attributes'] = array_merge($object['attributes'], $recover);
        static::assertEquals($expected, $object);
    }

    /**
     * Data provider for `testSetupRelationsMeta`
     *
     * @return array
     */
    public function setupRelationsProvider(): array
    {
        return [
            'simple' => [
                [
                    'relationsSchema' => [
                        'has_media' => [
                            'attributes' => [
                                'name' => 'has_media',
                                'label' => 'Has Media',
                                'inverse_name' => 'media_of',
                                'inverse_label' => 'Media Of',
                            ],
                        ],
                    ],
                    'resourceRelations' => [],
                    'objectRelations' => [
                        'main' => [
                            'has_media' => 'Has Media',
                        ],
                        'aside' => [],
                    ],
                ],
                [
                    'has_media' => [
                        'attributes' => [
                            'name' => 'has_media',
                            'label' => 'Has Media',
                            'inverse_name' => 'media_of',
                            'inverse_label' => 'Media Of',
                        ],
                    ],
                ],
                [
                    'has_media' => [],
                ],
            ],
            'inverse' => [
                [
                    'relationsSchema' => [
                        'media_of' => [
                            'attributes' => [
                                'name' => 'has_media',
                                'label' => 'Has Media',
                                'inverse_name' => 'media_of',
                                'inverse_label' => 'Media Of',
                            ],
                        ],
                    ],
                    'resourceRelations' => [],
                    'objectRelations' => [
                        'main' => [
                            'media_of' => 'Media Of',
                        ],
                        'aside' => [],
                    ],
                ],
                [
                    'media_of' => [
                        'attributes' => [
                            'name' => 'has_media',
                            'label' => 'Has Media',
                            'inverse_name' => 'media_of',
                            'inverse_label' => 'Media Of',
                        ],
                    ],
                ],
                [
                    'media_of' => [],
                ],
            ],
            'ordered' => [
                [
                    'relationsSchema' => [
                        'has_media' => [
                            'attributes' => [
                                'name' => 'has_media',
                                'label' => 'Has Media',
                                'inverse_name' => 'media_of',
                                'inverse_label' => 'Media Of',
                            ],
                        ],
                        'attach' => [
                            'attributes' => [
                                'name' => 'attach',
                                'label' => 'Attach',
                                'inverse_name' => 'attached_to',
                                'inverse_label' => 'Attached To',
                            ],
                        ],
                    ],
                    'resourceRelations' => [],
                    'objectRelations' => [
                        'main' => [
                            'attach' => 'Attach',
                            'has_media' => 'Has Media',
                        ],
                        'aside' => [
                        ],
                    ],
                ],
                [
                    'has_media' => [
                        'attributes' => [
                            'name' => 'has_media',
                            'label' => 'Has Media',
                            'inverse_name' => 'media_of',
                            'inverse_label' => 'Media Of',
                        ],
                    ],
                    'attach' => [
                        'attributes' => [
                            'name' => 'attach',
                            'label' => 'Attach',
                            'inverse_name' => 'attached_to',
                            'inverse_label' => 'Attached To',
                        ],
                    ],
                ],
                [
                    'has_media' => [],
                    'attach' => [],
                ],
                [
                    'main' => [
                        'attach',
                    ],
                    'aside' => [
                    ],
                ],
            ],
            'hidden' => [
                [
                    'relationsSchema' => [
                        'has_media' => [
                            'attributes' => [
                                'name' => 'has_media',
                                'label' => 'Has Media',
                                'inverse_name' => 'media_of',
                                'inverse_label' => 'Media Of',
                            ],
                        ],
                    ],
                    'resourceRelations' => [],
                    'objectRelations' => [
                        'main' => [
                            'has_media' => 'Has Media',
                        ],
                        'aside' => [
                        ],
                    ],
                ],
                [
                    'has_media' => [
                        'attributes' => [
                            'name' => 'has_media',
                            'label' => 'Has Media',
                            'inverse_name' => 'media_of',
                            'inverse_label' => 'Media Of',
                        ],
                    ],
                    'attach' => [
                        'attributes' => [
                            'name' => 'attach',
                            'label' => 'Attach',
                            'inverse_name' => 'attached_to',
                            'inverse_label' => 'Attached To',
                        ],
                    ],
                ],
                [
                    'has_media' => [],
                    'attach' => [],
                ],
                [
                    'main' => [
                        'attach',
                    ],
                    'aside' => [
                    ],
                ],
                ['attach'],
            ],
            'readonly' => [
                [
                    'relationsSchema' => [
                        'has_media' => [
                            'attributes' => [
                                'name' => 'has_media',
                                'label' => 'Has Media',
                                'inverse_name' => 'media_of',
                                'inverse_label' => 'Media Of',
                            ],
                            'readonly' => true,
                        ],
                    ],
                    'resourceRelations' => [],
                    'objectRelations' => [
                        'main' => [
                            'has_media' => 'Has Media',
                        ],
                        'aside' => [],
                    ],
                ],
                [
                    'has_media' => [
                        'attributes' => [
                            'name' => 'has_media',
                            'label' => 'Has Media',
                            'inverse_name' => 'media_of',
                            'inverse_label' => 'Media Of',
                        ],
                    ],
                ],
                [
                    'has_media' => [],
                ],
                [],
                [],
                ['has_media'],
            ],
        ];
    }

    /**
     * Test `setupRelationsMeta` method
     *
     * @dataProvider setupRelationsProvider
     * @covers ::setupRelationsMeta()
     * @covers ::relationLabels()
     * @param array $expected Expected result.
     * @param array $schema Schema array.
     * @param array $relationships Relationships array.
     * @param array $order Order array.
     * @param array $hidden Hidden array.
     * @param array $readonly Readonly array.
     * @return void
     */
    public function testSetupRelationsMeta(array $expected, array $schema, array $relationships, array $order = [], array $hidden = [], array $readonly = []): void
    {
        $this->Modules->setupRelationsMeta($schema, $relationships, $order, $hidden, $readonly);

        $viewVars = $this->Modules->getController()->viewBuilder()->getVars();

        static::assertEquals(array_keys($expected), array_keys($viewVars));

        foreach ($expected as $key => $value) {
            static::assertEquals($value, $viewVars[$key]);
        }
    }

    /**
     * Test `relatedTypes` method
     *
     * @return void
     * @covers ::relatedTypes()
     */
    public function testRelatedTypes(): void
    {
        $schema = [
            'has_media' => [
                'attributes' => [
                    'name' => 'has_media',
                    'inverse_name' => 'media_of',
                ],
                'left' => ['documents'],
                'right' => ['media'],
            ],
            'media_of' => [
                'attributes' => [
                    'name' => 'has_media',
                    'inverse_name' => 'media_of',
                ],
                'left' => ['media'],
                'right' => ['documents'],
            ],
        ];

        $types = $this->Modules->relatedTypes($schema, 'has_media');
        static::assertEquals(['media'], $types);
        $types = $this->Modules->relatedTypes($schema, 'media_of');
        static::assertEquals(['documents'], $types);
    }

    /**
     * Provider for `testRelationsSchema`.
     *
     * @return array
     */
    public function relationsSchemaProvider(): array
    {
        return [
            'empty data' => [
                [], // schema
                [], // relationships
                [], // expected
            ],
            'no right data' => [
                [
                    'hates' => [
                        'left' => ['elefants'],
                    ],
                    'loves' => [
                        'left' => ['robots'],
                    ],
                ], // schema
                [
                    'hates' => [],
                    'loves' => [],
                ], // relationships
                [
                    'hates' => [
                        'left' => ['elefants'],
                    ],
                    'loves' => [
                        'left' => ['robots'],
                    ],
                ], // expected
            ],
            'full example' => [
                [
                    'hates' => [
                        'left' => ['elefants'],
                        'right' => ['mices'],
                    ],
                    'loves' => [
                        'left' => ['robots'],
                        'right' => ['objects'],
                    ],
                ], // schema
                [
                    'hates' => [],
                    'loves' => [],
                ], // relationships
                [
                    'hates' => [
                        'left' => ['elefants'],
                        'right' => ['mices'],
                    ],
                    'loves' => [
                        'left' => ['robots'],
                        'right' => ['cats', 'dogs', 'elefants', 'mices'],
                    ],
                ], // expected
            ],
            'readonly' => [
                [
                    'hates' => [
                        'left' => ['elefants'],
                        'right' => ['mices'],
                    ],
                    'loves' => [
                        'left' => ['robots'],
                        'right' => ['objects'],
                    ],
                ], // schema
                [
                    'hates' => [
                        'readonly' => true,
                    ],
                    'loves' => [],
                ], // relationships
                [
                    'hates' => [
                        'left' => ['elefants'],
                        'right' => ['mices'],
                        'readonly' => true,
                    ],
                    'loves' => [
                        'left' => ['robots'],
                        'right' => ['cats', 'dogs', 'elefants', 'mices'],
                    ],
                ], // expected
            ],
        ];
    }

    /**
     * Test `relationsSchema` method
     *
     * @param array $schema The schema
     * @param array $relationships The relationships
     * @param array $expected The expected result
     * @return void
     * @dataProvider relationsSchemaProvider()
     * @covers ::relationsSchema()
     */
    public function testRelationsSchema(array $schema, array $relationships, array $expected): void
    {
        // call private method using AppControllerTest->invokeMethod
        $test = new AppControllerTest();
        $actual = $test->invokeMethod($this->MyModules, 'relationsSchema', [$schema, $relationships]);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for testSaveObjects
     *
     * @return array
     */
    public function saveObjectsProvider(): array
    {
        return [
            'empty data' => [
                [], // objects
                [], // expected
            ],
            'empty attributes' => [
                [['attributes' => []]], // objects
                [['attributes' => []]], // expected
            ],
            'full example' => [
                [
                    [
                        'type' => 'documents',
                        'attributes' => [
                            'title dummy one',
                            'status' => 'on',
                            'something-empty' => '',
                            'something-not-empty' => 'not empty',
                        ],
                    ],
                    [
                        'type' => 'documents',
                        'attributes' => [
                            'title dummy two',
                            'status' => 'on',
                            'something-empty' => '',
                            'something-not-empty' => 'not empty',
                        ],
                    ],
                ], // objects
                [
                    [
                        'type' => 'documents',
                        'attributes' => [
                            'title dummy one',
                            'status' => 'on',
                            'something-empty' => '',
                            'something-not-empty' => 'not empty',
                        ],
                    ],
                    [
                        'type' => 'documents',
                        'attributes' => [
                            'title dummy two',
                            'status' => 'on',
                            'something-empty' => '',
                            'something-not-empty' => 'not empty',
                        ],
                    ],
                ], // expected
            ],
        ];
    }

    /**
     * Test `saveObjects`
     *
     * @param array $objects The test objects
     * @param array $expected The expected data
     * @return void
     * @dataProvider saveObjectsProvider
     * @covers ::saveObjects()
     * @covers ::saveObject()
     */
    public function testSaveObjects(array $objects, array $expected): void
    {
        $this->setupApi();
        $this->Modules->saveObjects($objects);
        foreach ($expected as $index => &$exp) {
            if (!empty($exp['attributes'])) {
                $object = $objects[$index];
                static::assertArrayHasKey('id', $object);
                static::assertNotNull($object['id']);
                $exp['id'] = $object['id'];
            }
        }
        static::assertEquals($expected, $objects);
    }

    /**
     * Data provider for testSaveObject
     *
     * @return array
     */
    public function saveObjectProvider(): array
    {
        return [
            'empty data' => [
                [], // object
                [], // expected
            ],
            'empty attributes' => [
                ['attributes' => []], // object
                ['attributes' => []], // expected
            ],
            'full example' => [
                [
                    'type' => 'documents',
                    'attributes' => [
                        'status' => 'on',
                        'something-empty' => '',
                        'something-not-empty' => 'not empty',
                    ],
                ], // object
                [
                    'type' => 'documents',
                    'attributes' => [
                        'status' => 'on',
                        'something-empty' => '',
                        'something-not-empty' => 'not empty',
                    ],
                ], // expected
            ],
        ];
    }

    /**
     * Test `saveObject`
     *
     * @param array $object The test object
     * @param array $expected The expected data
     * @return void
     * @dataProvider saveObjectProvider
     * @covers ::saveObject()
     */
    public function testSaveObject(array $object, array $expected): void
    {
        $this->setupApi();
        $this->Modules->saveObject($object);
        if (!empty($expected['attributes'])) {
            static::assertArrayHasKey('id', $object);
            static::assertNotNull($object['id']);
            $expected['id'] = $object['id'];
        }
        static::assertEquals($expected, $object);
    }

    /**
     * Data provider for `testSaveRelated`.
     *
     * @return array
     */
    public function saveRelatedProvider(): array
    {
        $dummy = ['id' => 123, 'type' => 'dummies'];

        return [
            'bad request exception' => [
                111, // id
                'dummies', // type
                [
                    [
                        'method' => 'wrongMethod',
                        'relation' => 'see_also',
                        'relatedIds' => [$dummy],
                    ],
                ], // relatedData
                new BadRequestException(__('Bad related data method')), // expected
            ],
            'addRelated see_also' => [
                111, // id
                'dummies', // type
                [
                    [
                        'method' => 'addRelated',
                        'relation' => 'see_also',
                        'relatedIds' => [$dummy],
                    ],
                ], // relatedData
                'addRelated', // expected
            ],
            'removeRelated see_also' => [
                111, // id
                'dummies', // type
                [
                    [
                        'method' => 'removeRelated',
                        'relation' => 'see_also',
                        'relatedIds' => [$dummy],
                    ],
                ], // relatedData
                'removeRelated', // expected
            ],
            'replaceRelated see_also' => [
                111, // id
                'dummies', // type
                [
                    [
                        'method' => 'replaceRelated',
                        'relation' => 'see_also',
                        'relatedIds' => [$dummy],
                    ],
                ], // relatedData
                'replaceRelated', // expected
            ],
            'folders children not folders' => [
                111, // id
                'folders', // type
                [
                    [
                        'method' => 'addRelated',
                        'relation' => 'children',
                        'relatedIds' => [$dummy],
                    ],
                ], // relatedData
                'addRelated', // expected
            ],
            'folders children position' => [
                111, // id
                'folders', // type
                [
                    [
                        'method' => 'addRelated',
                        'relation' => 'children',
                        'relatedIds' => [
                            [
                                'id' => 123,
                                'type' => 'folders',
                                'meta' => ['relation' => ['position' => 1]],
                            ],
                            [
                                'id' => 124,
                                'type' => 'folders',
                                'meta' => ['relation' => ['position' => 2]],
                            ],
                        ],
                    ],
                ], // relatedData
                'replaceRelated', // expected
            ],
            'folders children folders' => [
                222, // id
                'folders', // type
                [
                    [
                        'method' => 'addRelated',
                        'relation' => 'children',
                        'relatedIds' => [['id' => 123, 'type' => 'folders']],
                    ],
                ], // relatedData
                'replaceRelated', // expected
            ],
            'folders children mixed' => [
                333, // id
                'folders', // type
                [
                    [
                        'method' => 'removeRelated',
                        'relation' => 'children',
                        'relatedIds' => [['id' => 123, 'type' => 'folders'], ['id' => 456, 'type' => 'dummies']],
                    ],
                ], // relatedData
                'removeRelated', // expected
            ],
        ];
    }

    /**
     * Test `saveRelated`
     *
     * @param int $id Object ID
     * @param string $type Object type
     * @param array $relatedData Related objects data
     * @param mixed $expected The expected result
     * @return void
     * @dataProvider saveRelatedProvider
     * @covers ::saveRelated()
     * @covers ::folderChildrenRelated()
     * @covers ::folderChildrenRemove()
     */
    public function testSaveRelated(int $id, string $type, array $relatedData, $expected): void
    {
        if ($expected instanceof \Exception) {
            $this->expectException(get_class($expected));
            $this->expectExceptionCode($expected->getCode());
            $this->expectExceptionMessage($expected->getMessage());
        }
        $actual = 'none';
        // Setup mock API client.
        $apiClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs(['https://media.example.org'])
            ->getMock();
        $apiClient->method('addRelated')
            ->will($this->returnCallback(function () use (&$actual) {
                $actual = 'addRelated';
            }));
        $apiClient->method('removeRelated')
            ->will($this->returnCallback(function () use (&$actual) {
                $actual = 'removeRelated';
            }));
        $apiClient->method('replaceRelated')
            ->will($this->returnCallback(function () use (&$actual) {
                $actual = 'replaceRelated';
            }));
        ApiClientProvider::setApiClient($apiClient);

        $this->Modules->saveRelated((string)$id, $type, $relatedData);
        static::assertEquals($expected, $actual);
    }
}
