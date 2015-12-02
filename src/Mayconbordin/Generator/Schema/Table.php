<?php namespace Mayconbordin\Generator\Schema;

class Table
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * Table constructor.
     * @param string $name
     * @param array $fields
     */
    public function __construct($name, array $fields)
    {
        $this->name   = $name;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param Field $field
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasField($name) {
        return isset($this->fields[$name]);
    }

    /**
     * @param $name
     * @return null|Field
     */
    public function getField($name)
    {
        if ($this->hasField($name)) {
            return $this->fields[$name];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasForeignKeys()
    {
        foreach ($this->fields as $field) {
            if ($field->hasForeign()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the table is a pivot table or not based on the absence of an 'id' field and the presence of two primary
     * keys and at least two foreign keys.
     *
     * @return bool
     */
    public function isPivot()
    {
        $numPk = 0;
        $numFk = 0;

        foreach ($this->fields as $field) {
            if ($field->isPrimary())  $numPk++;
            if ($field->hasForeign()) $numFk++;
        }

        return (!isset($this->fields['id']) && $numPk == 2 && $numFk >= 2);
    }
}