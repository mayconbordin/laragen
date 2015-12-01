<?php namespace Mayconbordin\Generator\Schema;

use Mayconbordin\Generator\Exceptions\InvalidFieldTypeException;

class Field
{
    const TYPES = ['bigincrements', 'biginteger', 'binary', 'boolean', 'char', 'date', 'datetime', 'decimal', 'double',
                   'enum', 'float', 'increments', 'integer', 'json', 'jsonb', 'longtext', 'mediumtext', 'mediuminteger',
                   'morphs', 'nullabletimestamps', 'remembertoken', 'smallinteger', 'softdeletes', 'string', 'text',
                   'time', 'tinyinteger', 'timestamp', 'timestamps', 'uuid'];

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
     * @var null|array
     */
    protected $foreign  = null;

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
            $this->setForeign($foreign['on'], $foreign['references']);
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
        if (!in_array($type, self::TYPES)) {
            throw new InvalidFieldTypeException($type);
        }

        $this->type = $type;
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
     * @return array|null
     */
    public function getForeign()
    {
        return $this->foreign;
    }

    /**
     * @param string $table The table that is being referenced
     * @param string $field The field that is being referenced
     */
    public function setForeign($table, $field = 'id')
    {
        $this->foreign = ['references' => $field, 'on' => $table];
    }

    /**
     * @return string
     */
    function __toString()
    {
        return 'Field{name='.$this->name.', type='.$this->type
               . ((sizeof($this->arguments) > 0) ? '(' . implode(', ', $this->arguments) . ')' : '')
               . ', unique='.(($this->unique) ? 'true' : 'false').', index='.(($this->index) ? 'true' : 'false')
               .', default='.(($this->default == null) ? 'null' : $this->default).', nullable='.(($this->nullable) ? 'true' : 'false')
               . ', unsigned='.(($this->unsigned) ? 'true' : 'false')
               .(($this->foreign != null) ? ', foreign={references='.$this->foreign['references'].', on='.$this->foreign['on'].'}' : '')
               . '}';
    }


}