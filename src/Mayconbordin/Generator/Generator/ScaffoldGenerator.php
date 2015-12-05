<?php namespace Mayconbordin\Generator\Generator;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mayconbordin\Generator\Exceptions\FileAlreadyExistsException;
use Mayconbordin\Generator\FormDumpers\FieldsDumper;
use Mayconbordin\Generator\FormDumpers\TableDumper;
use Mayconbordin\Generator\Scaffolders\ControllerScaffolder;
use Mayconbordin\Generator\Schema\Table;

class ScaffoldGenerator
{
    /**
     * The illuminate command instance.
     *
     * @var \Illuminate\Console\Command
     */
    protected $console;

    /**
     * The laravel instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $laravel;

    /**
     * The array of view names being created.
     *
     * @var array
     */
    protected $views = ['index', 'edit', 'show', 'create', 'form'];

    /**
     * Indicates the migration has been migrated.
     *
     * @var bool
     */
    protected $migrated = false;

    /**
     * @var array
     */
    protected $schema;

    /**
     * The constructor.
     *
     * @param Command $console
     * @param array $schema
     */
    public function __construct(Command $console, array $schema)
    {
        $this->console = $console;
        $this->laravel = $console->getLaravel();

        $this->schema  = $schema;
    }

    /**
     * Get entity name for the table.
     *
     * @param Table $table
     * @return string
     */
    public function getEntity(Table $table)
    {
        return strtolower(str_singular($table->getName()));
    }

    /**
     * Get entities name for the table.
     *
     * @param Table $table
     * @return string
     */
    public function getEntities(Table $table)
    {
        return str_plural($this->getEntity($table));
    }

    /**
     * Get controller name for the table.
     *
     * @param Table $table
     * @return string
     */
    public function getControllerName(Table $table)
    {
        $controller = Str::studly($this->getEntities($table)).'Controller';

        if ($this->console->option('prefix')) {
            $controller = Str::studly($this->getPrefix('/')).$controller;
        }

        return str_replace('/', '\\', $controller);
    }

    /**
     * Get repository name for the table.
     *
     * @param Table $table
     * @return string
     */
    public function getRepositoryName(Table $table)
    {
        $repository = Str::studly($this->getEntity($table)).'Repository';
        return str_replace('/', '\\', $repository);
    }

    /**
     * Get all the tables in the schema.
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->schema;
    }

    /**
     * Get only the main tables from the schema, i.e. those that are not pivot tables.
     *
     * @return array
     */
    public function getMainTables()
    {
        return array_filter($this->schema, function($table) {
            return !$table->isPivot();
        });
    }

    /**
     * Get only the pivot tables from the schema.
     *
     * @return array
     */
    public function getPivotTables()
    {
        return array_filter($this->schema, function($table) {
            return $table->isPivot();
        });
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $message
     *
     * @return string
     */
    public function confirm($message)
    {
        if ($this->console->option('no-question')) {
            return true;
        }

        return $this->console->confirm($message);
    }

    /**
     * Generate model.
     */
    public function generateModels()
    {
        if (!$this->confirm('Do you want to create the models?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            $fields = $table->serializeFields();

            $this->console->call('generate:model', [
                'name'       => $this->getEntity($table),
                '--fillable' => $fields,
                '--fields'   => $fields,
                '--force'    => $this->console->option('force'),
            ]);
        }
    }

    /**
     * Generate seed.
     */
    public function generateSeeds()
    {
        if (!$this->confirm('Do you want to create the database seeder classes?')) {
            return;
        }

        foreach ($this->getAllTables() as $table) {
            $this->console->call('generate:seed', [
                'name'    => $this->getEntities($table),
                '--force' => $this->console->option('force'),
            ]);
        }
    }

    /**
     * Generate migration.
     */
    public function generateMigrations()
    {
        if (!$this->confirm('Do you want to create a migration?')) {
            return;
        }

        if ($this->console->argument('name')) {
            foreach ($this->getAllTables() as $table) {
                $this->console->call('generate:migration', [
                    'name'     => "create_{$this->getEntities($table)}_table",
                    '--fields' => $table->serializeFields(),
                    '--force'  => $this->console->option('force'),
                ]);
            }
        } else {
            $this->console->call('generate:migration', [
                '--connection'        => $this->console->option('connection'),
                '--tables'            => $this->console->option('tables'),
                '--ignore'            => $this->console->option('ignore'),
                '--defaultIndexNames' => $this->console->option('defaultIndexNames'),
                '--defaultFKNames'    => $this->console->option('defaultFKNames'),
                '--force'             => $this->console->option('force'),
            ]);
        }
    }

    /**
     * Generate controller.
     */
    public function generateControllers()
    {
        if (!$this->confirm('Do you want to generate the controllers?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            $this->console->call('generate:controller', [
                'name'         => $this->getControllerName($table),
                '--force'      => $this->console->option('force'),
                '--repository' => $this->console->option('repository'),
                '--scaffold'   => !$this->console->option('repository'),
            ]);
        }
    }

    /**
     * Generate repositories.
     */
    public function generateRepositories()
    {
        if (!$this->console->option('repository') || !$this->confirm('Do you want to generate the repositories?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            $this->console->call('generate:repository', [
                'name'    => $this->getRepositoryName($table),
                '--force' => $this->console->option('force')
            ]);
        }
    }

    /**
     * Get view layout.
     *
     * @return string
     */
    public function getViewLayout()
    {
        return $this->getPrefix('/').'layouts/master';
    }

    /**
     * Generate a view layout.
     */
    public function generateViewLayout()
    {
        if ($this->confirm('Do you want to create master view?')) {
            $this->console->call('generate:view', [
                'name' => $this->getViewLayout(),
                '--master' => true,
                '--force' => $this->console->option('force'),
            ]);
        }
    }

    /**
     * Get controller scaffolder instance.
     *
     * @param Table $table
     * @return ControllerScaffolder
     */
    public function getControllerScaffolder(Table $table)
    {
        return new ControllerScaffolder($this->getEntity($table), $this->getPrefix());
    }

    /**
     * Get form generator instance.
     *
     * @param Table $table
     * @return FormGenerator
     */
    public function getFormGenerator(Table $table)
    {
        return new FormGenerator([
            'table'  => $this->getEntities($table),
            'fields' => $table->serializeFields()
        ]);
    }

    /**
     * Get table dumper.
     *
     * @param Table $table
     * @return TableDumper|FieldsDumper
     */
    public function getTableDumper(Table $table)
    {
        if ($this->migrated) {
            return new TableDumper($this->getEntities($table));
        }

        return new FieldsDumper($table->getFields());
    }

    /**
     * Generate views.
     */
    public function generateViews()
    {
        $this->generateViewLayout();

        if (!$this->confirm('Do you want to create view resources?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            foreach ($this->views as $view) {
                $this->generateView($table, $view);
            }
        }
    }

    /**
     * Generate a scaffold view.
     *
     * @param Table $table
     * @param string $view
     * @throws FileAlreadyExistsException
     */
    public function generateView(Table $table, $view)
    {
        $generator = new ViewGenerator([
            'name'     => $this->getPrefix('/').$this->getEntities($table).'/'.$view,
            'extends'  => str_replace('/', '.', $this->getViewLayout()),
            'template' => __DIR__.'/../Stubs/scaffold/views/'.$view.'.stub',
            'force'    => $this->console->option('force'),
        ]);

        $tableDumper = $this->getTableDumper($table);

        $generator->appendReplacement(array_merge($this->getControllerScaffolder($table)->toArray(), [
            'lower_plural_entity'    => strtolower($this->getEntities($table)),
            'studly_singular_entity' => Str::studly($this->getEntity($table)),
            'form'                   => $this->getFormGenerator($table)->render(),
            'table_heading'          => $tableDumper->toHeading(),
            'table_body'             => $tableDumper->toBody($this->getEntity($table)),
            'show_body'              => $tableDumper->toRows($this->getEntity($table)),
        ]));

        $generator->run();

        $this->console->info("View {$generator->getName()} created successfully.");
    }

    /**
     * Append new route.
     */
    public function appendRoutes()
    {
        if (!$this->confirm('Do you want to append new routes?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            $contents = $this->laravel['files']->get($path = app_path('Http/routes.php'));
            $contents .= PHP_EOL . "Route::resource('{$this->getRouteName($table)}', '{$this->getControllerName($table)}');";

            $this->laravel['files']->put($path, $contents);

            $this->console->info("Route for {$this->getEntity($table)} appended successfully.");
        }
    }

    /**
     * Get route name for the table.
     *
     * @param Table $table
     * @return string
     */
    public function getRouteName(Table $table)
    {
        $route = $this->getEntities($table);

        if ($this->console->option('prefix')) {
            $route = strtolower($this->getPrefix('/')).$route;
        }

        return $route;
    }

    /**
     * Get prefix name.
     *
     * @param string|null $suffix
     *
     * @return string|null
     */
    public function getPrefix($suffix = null)
    {
        $prefix = $this->console->option('prefix');

        return $prefix ? $prefix.$suffix : null;
    }

    /**
     * Run the migrations.
     */
    public function runMigration()
    {
        if ($this->confirm('Do you want to run all migration now?')) {
            $this->migrated = true;

            $this->console->call('migrate', [
                '--force' => $this->console->option('force'),
            ]);
        }
    }

    /**
     * Generate request classes.
     */
    public function generateRequests()
    {
        if (!$this->confirm('Do you want to create form request classes?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            foreach (['Create', 'Update'] as $request) {
                $name = $this->getPrefix('/') . $this->getEntities($table) . '/' . $request . Str::studly($this->getEntity($table)) . 'Request';

                $this->console->call('generate:request', [
                    'name'       => $name,
                    '--scaffold' => true,
                    '--auth'     => true,
                    '--fields'   => $table->serializeFields(),
                    '--force'    => $this->console->option('force'),
                ]);
            }
        }
    }

    /**
     * Get a serialized string of language translations.
     *
     * @param Table $table
     * @return string
     */
    public function getLangTranslations(Table $table)
    {
        $translations = [
            'title'   => Str::studly($this->getEntity($table)).'|'.Str::studly($this->getEntities($table)),
            'created' => Str::studly($this->getEntity($table)).' created successfully',
            'updated' => Str::studly($this->getEntity($table)).' updated successfully',
            'deleted' => Str::studly($this->getEntity($table)).' deleted successfully',
        ];

        $result = "\"";

        foreach ($translations as $key => $value) {
            $result .= "$key='$value', ";
        }

        $result .= "\"";

        return $result;
    }

    /**
     * Generate request classes.
     */
    public function generateLangResources()
    {
        if (!$this->confirm('Do you want to create the language resource files?')) {
            return;
        }

        foreach ($this->getMainTables() as $table) {
            $this->console->call('generate:lang', [
                'name'           => $this->getEntity($table),
                '--languages'    => $this->console->option('languages'),
                '--translations' => $this->getLangTranslations($table)
            ]);
        }
    }

    /**
     * Run the generator.
     */
    public function run()
    {
        $this->generateModels();
        $this->generateMigrations();
        $this->generateRepositories();
        $this->generateSeeds();
        $this->generateRequests();
        $this->generateControllers();
        $this->runMigration();
        $this->generateViews();
        $this->generateLangResources();
        $this->appendRoutes();
    }
}
