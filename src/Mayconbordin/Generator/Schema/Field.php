<?php namespace Mayconbordin\Generator\Schema;

use Mayconbordin\Generator\Exceptions\InvalidFieldTypeException;

class Field
{
    const TYPES = [
        'bigincrements'      => 'bigIncrements',
        'biginteger'         => 'bigInteger',
        'binary'             => 'binary',
        'boolean'            => 'boolean',
        'char'               => 'char',
        'date'               => 'date',
        'datetime'           => 'datetime',
        'decimal'            => 'decimal',
        'double'             => 'double',
        'enum'               => 'enum',
        'float'              => 'float',
        'increments'         => 'increments',
        'integer'            => 'integer',
        'json'               => 'json',
        'jsonb'              => 'jsonb',
        'longtext'           => 'longText',
        'mediumtext'         => 'mediumText',
        'mediuminteger'      => 'mediumInteger',
        'morphs'             => 'morphs',
        'nullabletimestamps' => 'nullableTimestamps',
        'remembertoken'      => 'rememberToken',
        'smallinteger'       => 'smallInteger',
        'softdeletes'        => 'softDeletes',
        'string'             => 'string',
        'text'               => 'text',
        'time'               => 'time',
        'tinyinteger'        => 'tinyInteger',
        'timestamp'          => 'timestamp',
        'timestamps'         => 'timestamps',
        'uuid'               => 'uuid'
    ];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var bool
     */
    protected $unique   = false;

    /**
     * @var bool
     */
    protected $index    = false;

    /**
     * @var null|mixed
     */
    protected $default  = null;

    /**
     * @var bool
     */
    protected $nullable = true;

    /**
     * @var bool
     */
    protected $unsigned = false;

    /**
     * @var null|Foreign
     */
    protected $foreign  = null;

    /**
     * @var bool If the field is a primary key, used only for composite keys.
     */
    protected $primary = false;

    /**
     * Field constructor.
     * @param array $segments
     */
    public function __construct(array $segments = null)
    {
        if ($segments != null) {
            $this->loadFromSegments($segments);
        }
    }

    /**
     * Load the field values from a field segment.
     *
     * @param array $segments
     * @throws InvalidFieldTypeException
     */
    public function loadFromSegments(array $segments)
    {
        $this->setName(array_get($segments, 'name'));
        $this->setType(array_get($segments, 'type'));
        $this->setArguments(array_get($segments, 'arguments', []));

        $options = array_get($segments, 'options', []);

        $this->setUnique(array_get($options, 'unique', false));
        $this->setIndex(array_get($options, 'index', false));
        $this->setDefault(array_get($options, 'default', null));
        $this->setUnsigned(array_get($options, 'unsigned', false));
        $this->setNullable(array_get($options, 'nullable', true));

        $foreign = array_get($options, 'foreign', null);

        if ($foreign != null) {
            $this->setForeign($foreign['on'], array_get($foreign, 'references', 'id'), array_get($foreign, 'name', null));
        }
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws InvalidFieldTypeException
     */
    public function setType($type)
    {
        $type = strtolower($type);

        if (!in_array($type, array_keys(self::TYPES))) {
            throw new InvalidFieldTypeException($type);
        }

        $this->type = array_get(self::TYPES, $type);
    }

    /**
     * @return bool
     */
    public function hasArguments()
    {
        return sizeof($this->arguments) > 0;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return boolean
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * @param boolean $unique
     */
    public function setUnique($unique)
    {
        $this->unique = $unique;
    }

    /**
     * @return boolean
     */
    public function isIndex()
    {
        return $this->index;
    }

    /**
     * @param boolean $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function hasDefault()
    {
        return $this->default != null;
    }

    /**
     * @param mixed|null $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @param boolean $nullable
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;
    }

    /**
     * @return boolean
     */
    public function isUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * @param boolean $unsigned
     */
    public function setUnsigned($unsigned)
    {
        $this->unsigned = $unsigned;
    }

    /**
     * @return bool
     */
    public function hasForeign()
    {
        return $this->foreign != null;
    }

    /**
     * @return Foreign|null
     */
    public function getForeign()
    {
        return $this->foreign;
    }

    /**
     * @param string $table The table that is being referenced
     * @param string $field The field that is being referenced
     */
    public function setForeign($table, $field = 'id', $name = null)
    {
        $this->foreign = new Foreign($table, $field, $name);
    }

    /**
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->primary;
    }

    /**
     * @param boolean $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return 'Field {name='.$this->name.', type='.$this->type
               . ((sizeof($this->arguments) > 0) ? '(' . implode(', ', $this->arguments) . ')' : '')
               . ', unique='.(($this->unique) ? 'true' : 'false').', index='.(($this->index) ? 'true' : 'false')
               . ', default='.(($this->default == null) ? 'null' : $this->default).', nullable='.(($this->nullable) ? 'true' : 'false')
               . ', unsigned='.(($this->unsigned) ? 'true' : 'false')
               . (is_null($this->foreign) ? '' : $this->foreign->__toString())
               . '}';
    }


}