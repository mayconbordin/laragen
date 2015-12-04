<?php namespace Mayconbordin\Generator\Generator;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mayconbordin\Generator\Exceptions\FileAlreadyExistsException;

use \Config;

abstract class Generator
{
    use AppNamespaceDetectorTrait;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The array of options.
     *
     * @var array
     */
    protected $options;

    /**
     * The shortname of stub.
     *
     * @var string
     */
    protected $stub;

    /**
     * @var string
     */
    protected $entity;

    /**
     * Create new instance of this class.
     *
     * @param string $entity
     * @param array $options [ name=The name of the entity;
     *                         force=Whether the file should be created even if it already exists ]
     *
     */
    public function __construct($entity, array $options = array())
    {
        $this->entity     = $entity;
        $this->filesystem = new Filesystem();
        $this->options    = $options;
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set the filesystem instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get stub template for generated file.
     *
     * @return string
     */
    public function getStub()
    {
        $stub = new Stub($this->stub.'.stub', $this->getReplacements());

        $stub->setBasePath(__DIR__.'/../Stubs/');

        return $stub->render();
    }

    /**
     * Get template replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return [
            'class'          => $this->getClass(),
            'namespace'      => $this->getNamespace(),
            'root_namespace' => $this->getRootNamespace(),
        ];
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        if (Config::has("generator.{$this->entity}.path")) {
            return Config::get("generator.{$this->entity}.path");
        }

        return Config::get('generator.base_path', base_path());
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath().'/'.$this->getFileName().'.php';
    }

    /**
     * Get the name of the file to be saved on the filesystem.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getName();
    }

    /**
     * Get name input.
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->name;

        if (str_contains($this->name, '\\')) {
            $name = str_replace('\\', '/', $this->name);
        }

        if (str_contains($this->name, '/')) {
            $name = str_replace('/', '/', $this->name);
        }

        return Str::studly(str_replace(' ', '/', ucwords(str_replace('/', ' ', $name))));
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass()
    {
        return Str::studly(class_basename($this->getName()));
    }

    /**
     * Get paths of namespace.
     *
     * @return array
     */
    public function getSegments()
    {
        return explode('/', $this->getName());
    }

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return Config::get('generator.root_namespace', $this->getAppNamespace());
    }

    /**
     * Get class namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        $segments = $this->getSegments();

        array_pop($segments);

        if (Config::has("generator.{$this->entity}.namespace")) {
            $rootNamespace = Config::get("generator.{$this->entity}.namespace");
        } else {
            $rootNamespace = $this->getRootNamespace();
        }

        if ($rootNamespace == false || $rootNamespace == null) {
            return;
        }

        return 'namespace '.rtrim($rootNamespace.implode('\\', $segments), '\\').';';
    }

    /**
     * Setup some hook.
     */
    public function setUp()
    {
        //
    }

    /**
     * Run the generator.
     *
     * @return int
     *
     * @throws FileAlreadyExistsException
     */
    public function run()
    {
        $this->setUp();

        if ($this->filesystem->exists($path = $this->getPath()) && !$this->force) {
            throw new FileAlreadyExistsException($path);
        }

        if (!$this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0777, true, true);
        }

        return $this->filesystem->put($path, $this->getStub());
    }

    /**
     * Get options.
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Determinte whether the given key exist in options array.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Get value from options by given key.
     *
     * @param string      $key
     * @param string|null $default
     *
     * @return string
     */
    public function getOption($key, $default = null)
    {
        if (!$this->hasOption($key)) {
            return $default;
        }

        return $this->options[$key] ?: $default;
    }

    /**
     * Helper method for "getOption".
     *
     * @param string      $key
     * @param string|null $default
     *
     * @return string
     */
    public function option($key, $default = null)
    {
        return $this->getOption($key, $default);
    }

    /**
     * Handle call to __get method.
     *
     * @param string $key
     *
     * @return string|mixed
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->option($key);
    }
}
