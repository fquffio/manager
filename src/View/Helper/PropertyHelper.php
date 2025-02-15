<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2020 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace App\View\Helper;

use App\Utility\Translate;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * Helper class to generate properties html
 *
 * @property \App\View\Helper\SchemaHelper $Schema The schema helper
 * @property \Cake\View\Helper\FormHelper $Form The form helper
 */
class PropertyHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Form', 'Schema'];

    /**
     * Special paths to retrieve properties from related resources
     *
     * @var array
     */
    public const RELATED_PATHS = [
        'file_name' => 'relationships.streams.data.0.attributes.file_name',
        'mime_type' => 'relationships.streams.data.0.attributes.mime_type',
        'file_size' => 'relationships.streams.data.0.meta.file_size',
    ];

    /**
     * Special properties having their own custom schema type
     *
     * @var array
     */
    public const SPECIAL_PROPS_TYPE = [
        'categories' => 'categories',
        'relations' => 'relations',
        'file_size' => 'byte',
    ];

    /**
     * Generates a form control element for an object property by name, value and options.
     * Use SchemaHelper (@see \App\View\Helper\SchemaHelper) to get control options by schema model.
     * Use FormHelper (@see \Cake\View\Helper\FormHelper::control) to render control.
     *
     * @param string $name The property name
     * @param mixed|null $value The property value
     * @param array $options The form element options, if any
     * @param string|null $type The type, for others schemas
     * @return string
     */
    public function control(string $name, $value, array $options = [], ?string $type = null): string
    {
        $controlOptions = $this->Schema->controlOptions($name, $value, $this->schema($name, $type));
        $controlOptions['label'] = $this->fieldLabel($name, $type);
        if (Hash::get($controlOptions, 'class') === 'json') {
            $jsonKeys = (array)Configure::read('_jsonKeys');
            Configure::write('_jsonKeys', array_merge($jsonKeys, [$name]));
        }
        if (Hash::check($controlOptions, 'html')) {
            return (string)Hash::get($controlOptions, 'html', '');
        }

        return $this->Form->control($name, array_merge($controlOptions, $options));
    }

    /**
     * Return label for field by name and type.
     * If there's a config for the field and type, return it.
     * Return translation of name, otherwise.
     *
     * @param string $name The field name
     * @param string|null $type The object type
     * @return string
     */
    public function fieldLabel(string $name, ?string $type = null): string
    {
        $defaultLabel = Translate::get($name);
        $t = empty($type) ? $this->getView()->get('objectType') : $type;
        if (empty($t)) {
            return $defaultLabel;
        }
        $key = sprintf('Properties.%s.labels.fields.%s', $t, $name);

        return Configure::read($key, $defaultLabel);
    }

    /**
     * JSON Schema array of property name
     *
     * @param string $name The property name
     * @param string|null $type The type, for others schemas
     * @return array
     */
    public function schema(string $name, ?string $type = null): array
    {
        $schema = (array)$this->_View->get('schema');
        if (!empty($type)) {
            $schemas = (array)$this->_View->get('schemasByType');
            $schema = (array)Hash::get($schemas, $type);
        }
        $res = (array)Hash::get($schema, sprintf('properties.%s', $name));
        $default = array_filter([
            'type' => Hash::get(self::SPECIAL_PROPS_TYPE, $name),
            $name => Hash::get($schema, sprintf('%s', $name)),
        ]);
        if (in_array($name, self::SPECIAL_PROPS_TYPE)) {
            return $default;
        }

        return !empty($res) ? $res : $default;
    }

    /**
     * Get formatted property value of a resource or object.
     *
     * @param array $resource Resource or object data
     * @param string $property Property name
     * @return string
     */
    public function value(array $resource, string $property): string
    {
        $paths = array_filter([
            sprintf('attributes.%s', $property),
            sprintf('meta.%s', $property),
            (string)Hash::get(self::RELATED_PATHS, $property),
        ]);
        $value = '';
        foreach ($paths as $path) {
            if (Hash::check($resource, $path)) {
                $value = (string)Hash::get($resource, $path);
                break;
            }
        }

        return $this->Schema->format($value, $this->schema($property));
    }
}
