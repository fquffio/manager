<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2019 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\SchemaHelper;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Cake\View\View;

/**
 * {@see \App\View\Helper\SchemaHelper} Test Case
 *
 * @coversDefaultClass \App\View\Helper\SchemaHelper
 */
class SchemaHelperTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\View\Helper\SchemaHelper
     */
    public $Schema;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $view = new View();
        $this->Schema = new SchemaHelper($view);
        $view->set('objectType', 'dummies');

        Configure::write(
            'Control.handlers',
            array_merge(
                (array)\Cake\Core\Configure::read('Control.handlers'),
                [
                    'dummies' => [ // an object type
                        'descr' => [ // a field
                            'type' => 'string', // property type
                            'class' => 'App\Test\TestCase\View\Helper\PropertyHelperTest',
                            'method' => 'dummy',
                        ],
                    ],
                ]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        unset($this->Schema);

        parent::tearDown();
    }

    /**
     * Data provider for `testControlOptions` test case.
     *
     * @return array
     */
    public function controlOptionsSchemaProvider(): array
    {
        return [
            'text' => [
                // expected result
                [
                    'type' => 'text',
                    'value' => 'test',
                ],
                // schema type
                [
                    'type' => 'string',
                ],
                'name',
                'test',
            ],
            'status' => [
                // expected result
                [
                    'options' => [
                        ['value' => 'on', 'text' => __('On')],
                        ['value' => 'draft', 'text' => __('Draft')],
                        ['value' => 'off', 'text' => __('Off')],
                    ],
                    'templateVars' => [
                        'containerClass' => 'status',
                    ],
                    'type' => 'radio',
                    'value' => 'on',
                ],
                // schema type
                [
                    'type' => 'string',
                ],
                'status',
                'on',
            ],
            'password' => [
                // expected result
                [
                    'autocomplete' => 'new-password',
                    'class' => 'password',
                    'default' => '',
                    'placeholder' => __('new password'),
                    'value' => '',
                ],
                // schema type
                [
                    'type' => 'string',
                ],
                'password',
                '',
            ],
            'confirm-password' => [
                // expected result
                [
                    'autocomplete' => 'new-password',
                    'class' => 'confirm-password',
                    'default' => '',
                    'id' => 'confirm_password',
                    'label' => __('Retype password'),
                    'name' => 'confirm-password',
                    'placeholder' => __('confirm password'),
                    'type' => 'password',
                    'value' => '',
                ],
                // schema type
                [
                    'type' => 'string',
                ],
                'confirm-password',
                '',
            ],
            'json' => [
                // expected result
                [
                    'class' => 'json',
                    'type' => 'textarea',
                    'v-jsoneditor' => 'true',
                    'value' => json_encode('{ "example": { "this": "is", "an": "example" } }'),
                ],
                // schema type
                [
                    'type' => 'object',
                ],
                'extra',
                '{ "example": { "this": "is", "an": "example" } }',
            ],
            'title' => [
                // expected result
                [
                    'class' => 'title',
                    'templates' => [
                        'inputContainer' => '<div class="input title {{type}}{{required}}">{{content}}</div>',
                    ],
                    'type' => 'text',
                    'value' => 'test',
                ],
                // schema type
                [
                    'type' => 'string',
                    'contentMediaType' => 'text/html',
                ],
                'title',
                'test',
            ],
            'description' => [
                // expected result
                [
                    'type' => 'textarea',
                    'v-richeditor' => '""',
                    'value' => 'test',
                ],
                // schema type
                [
                    'type' => 'string',
                    'contentMediaType' => 'text/html',
                ],
                'description',
                'test',
            ],
            'body' => [
                // expected result
                [
                    'type' => 'textarea',
                    'v-richeditor' => '""',
                    'value' => 'test',
                ],
                // schema type
                [
                    'type' => 'string',
                    'contentMediaType' => 'text/html',
                ],
                'body',
                'test',
            ],
            'publish_start' => [
                // expected result
                [
                    'date' => 'true',
                    'time' => 'true',
                    'type' => 'text',
                    'templates' => [
                        'inputContainer' => '<div class="input datepicker {{type}}{{required}}">{{content}}</div>',
                    ],
                    'v-datepicker' => 'true',
                    'value' => 'test',
                ],
                // schema type
                [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'publish_start',
                'test',
            ],
            'enum' => [
                // expected result
                [
                    'options' => [
                        ['value' => 'good', 'text' => 'Good'],
                        ['value' => 'bad', 'text' => 'Bad'],
                    ],
                    'type' => 'select',
                ],
                // schema type
                [
                    'type' => 'string',
                    'enum' => [
                        'good',
                        'bad',
                    ],
                ],
                'enum',
                'good',
            ],
            'enum nullable' => [
                // expected result
                [
                    'options' => [
                        ['value' => '', 'text' => ''],
                        ['value' => 'good', 'text' => 'Good'],
                        ['value' => 'bad', 'text' => 'Bad'],
                    ],
                    'type' => 'select',
                ],
                // schema type
                [
                    'oneOf' => [
                        [
                            'type' => 'null',
                        ],
                        [
                            'type' => 'string',
                            'enum' => [
                                'good',
                                'bad',
                            ],
                        ],
                    ],
                ],
                'enum',
                'good',
            ],
            'checkbox' => [
                // expected result
                [
                    'checked' => true,
                    'type' => 'checkbox',
                ],
                // schema type
                [
                    'type' => 'boolean',
                ],
                'company',
                true,
            ],
            'array multiple checkbox' => [
                // expected result
                [
                    'multiple' => 'checkbox',
                    'options' => [
                        [
                            'value' => 'a',
                            'text' => 'A',
                        ],
                        [
                            'value' => 'b',
                            'text' => 'B',
                        ],
                        [
                            'value' => 'c',
                            'text' => 'C',
                        ],
                        [
                            'value' => 'd',
                            'text' => 'D',
                        ],
                    ],
                    'type' => 'select',
                ],
                // schema type
                [
                    'type' => 'array',
                    'oneOf' => [
                        [
                            'type' => 'null',
                        ],
                        [
                            'type' => 'array',
                            'uniqueItems' => true,
                            'items' => [
                                'type' => 'string',
                                'enum' => ['a', 'b', 'c', 'd'],
                            ],
                        ],
                    ],
                ],
                'test_array',
                null,
            ],
            'custom handler' => [
                // expected result
                [
                    'html' => '<dummy>something</dummy>',
                ],
                // schema type
                [
                    'type' => 'string',
                ],
                'descr',
                'something',
            ],
        ];
    }

    /**
     * Test `controlOptions` method.
     *
     * @param array $expected Expected result.
     * @param array $schema The JSON schema
     * @param string $name The field name.
     * @param string|null $value The field value.
     * @return void
     *
     * @dataProvider controlOptionsSchemaProvider()
     * @covers ::controlOptions()
     */
    public function testControlOptions(array $expected, array $schema, string $name, ?string $value): void
    {
        $actual = $this->Schema->controlOptions($name, $value, $schema);

        static::assertEquals($expected, $actual);
    }

    /**
     * Test `lang` property
     *
     * @return void
     *
     * @covers ::controlOptions()
     */
    public function testLang()
    {
        Configure::write('Project.config.I18n', null);
        $actual = $this->Schema->controlOptions('lang', null, []);
        static::assertEquals('text', Hash::get($actual, 'type'));
        static::assertNull(Hash::get($actual, 'value'));

        $i18n = [
            'languages' => [
                'en' => 'English',
                'de' => 'German',
            ],
        ];
        Configure::write('Project.config.I18n', $i18n);
        $actual = $this->Schema->controlOptions('lang', null, []);

        $expected = [
            'options' => [
                [
                    'value' => 'en',
                    'text' => 'English',
                ],
                [
                    'value' => 'de',
                    'text' => 'German',
                ],
            ],
            'type' => 'select',
            'value' => null,
        ];
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testTranslatableFields` test case.
     *
     * @return array
     */
    public function translatableFieldsProvider(): array
    {
        return [
            'empty properties' => [
                [],
                [],
            ],
            'properties' => [
                [
                    'dummy' => [
                        'oneOf' => [
                            [
                                'type' => 'null',
                            ],
                            [
                                'type' => 'string',
                                'contentMediaType' => 'text/html',
                            ],
                        ],
                    ],
                    'description' => [
                        'type' => 'string',
                        'contentMediaType' => 'text/html',
                    ],
                    'title' => [
                        'type' => 'string',
                        'contentMediaType' => 'text/html',
                    ],
                ],
                [
                    'title',
                    'description',
                    'dummy',
                ],
            ],
        ];
    }

    /**
     * Test `translatableFields` method
     *
     * @param array $properties The properties
     * @param array $expected Expected result
     * @return void
     *
     * @dataProvider translatableFieldsProvider()
     * @covers ::translatableFields()
     */
    public function testTranslatableFields(array $properties, array $expected)
    {
        $actual = $this->Schema->translatableFields($properties);
        static::assertSame($expected, $actual);
    }

    /**
     * Data provider for `testFormat` test case.
     *
     * @return array
     */
    public function formatProvider(): array
    {
        return [
            'dumy' => [
                'dummy',
                'dummy',
                [
                    'oneOf' => [
                        [
                            'type' => 'null',
                        ],
                        [
                            'type' => 'string',
                            'contentMediaType' => 'text/html',
                        ],
                    ],
                ],
            ],
            'bool' => [
                'Yes',
                true,
                [
                    'type' => 'boolean',
                ],
            ],
            'date' => [
                '9/8/19, 12:00 AM',
                '2019-09-08',
                [
                    'type' => 'string',
                    'format' => 'date',
                ],
            ],
            'date time' => [
                '9/8/19, 4:35 PM',
                '2019-09-08T16:35:15+00',
                [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
            ],
            'json' => [
                '{"a":1,"b":2}',
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'type' => 'object',
                ],
            ],
            'type not in string, number, integer, boolean, array, object' => [
                'Dummy',
                'Dummy',
                [
                    'type' => 'dummy',
                ],
            ],
        ];
    }

    /**
     * Test `format` method
     *
     * @param array $properties The properties
     * @param array $expected Expected result
     * @return void
     *
     * @dataProvider formatProvider()
     * @covers ::format()
     * @covers ::formatBoolean()
     * @covers ::formatDate()
     * @covers ::formatDateTime()
     * @covers ::typeFromSchema()
     */
    public function testFormat($expected, $value, array $schema)
    {
        $actual = $this->Schema->format($value, $schema);
        static::assertSame($expected, $actual);
    }
}
