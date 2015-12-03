<?php namespace Mayconbordin\Generator\Helpers;

use Mayconbordin\Generator\Schema\Field;

class FieldValidationHelper
{
    /**
     * @param Field $field
     * @param string|null $tableName The name of the table may be used in case the field is unique.
     * @return array
     */
    public static function toRules(Field $field, $tableName = null)
    {
        $rules = [];

        if (self::isText($field)) {
            $rules[] = 'string';

            if ($field->hasArguments()) {
                $rules[] = 'max:'.$field->getArgument(0);
            }
        } else if (self::isInt($field)) {
            $rules[] = 'integer';
        } else if (self::isNumber($field)) {
            $rules[] = 'numeric';
        } else if (self::isDateTime($field)) {
            $rules[] = 'date';
        } else if (self::isJson($field)) {
            $rules[] = 'json';
        } else if (self::isBoolean($field)) {
            $rules[] = 'boolean';
        } else if (self::isEnum($field)) {
            $rules[] = 'in:'.implode(',', $field->getArguments());
        }

        if ($field->hasForeign()) {
            $rules[] = 'exists:'.$field->getForeign()->getOn().','.$field->getForeign()->getReferences();
        }

        if (!$field->isNullable()) {
            $rules[] = 'required';
        }

        if ($field->isUnique()) {
            $rules[] = 'unique:'.$tableName;
        }

        return $rules;
    }

    public static function isInt(Field $field)
    {
        return in_array($field->getType(), ['tinyInteger', 'smallInteger', 'integer', 'mediumInteger', 'bigInteger', 'bigIncrements', 'increments']);
    }

    public static function isNumber(Field $field)
    {
        return self::isInt($field) || in_array($field->getType(), ['decimal', 'float', 'double']);
    }

    public static function isDateTime(Field $field)
    {
        return in_array($field->getType(), ['dateTime', 'date', 'time', 'timestamp']);
    }

    public static function isText(Field $field)
    {
        return in_array($field->getType(), ['char', 'json', 'longText', 'mediumText', 'string', 'text']);
    }

    public static function isJson(Field $field)
    {
        return in_array($field->getType(), ['json']);
    }

    public static function isBoolean(Field $field)
    {
        return in_array($field->getType(), ['boolean']);
    }

    public static function isEnum(Field $field)
    {
        return in_array($field->getType(), ['enum']);
    }
}