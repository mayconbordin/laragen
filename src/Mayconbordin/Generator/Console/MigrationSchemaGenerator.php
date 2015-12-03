<?php namespace Mayconbordin\Generator\Generator;


use Mayconbordin\Generator\Database\SchemaGenerator;
use Mayconbordin\Generator\Parsers\NameParser;
use Mayconbordin\Generator\Parsers\SchemaParser;
use Mayconbordin\Generator\Schema\Table;

trait MigrationSchemaGenerator
{
    /**
     * @var SchemaGenerator
     */
    protected $schemaGenerator;

    protected function generateFromCommand()
    {
        $meta   = (new NameParser())->parse($this->argument('name'));
        $fields = (new SchemaParser())->parse($this->option('fields'));
        $table  = new Table($meta['table'], $fields);

        $generator = new MigrationGenerator([
            'name'   => $this->argument('name'),
            'action' => $meta['action'],
            'force'  => $this->option('force'),
            'table'  => $table
        ]);

        $generator->run();

        $this->info("Migration {$this->argument('name')} created successfully.");
    }

    protected function generateFromDatabase()
    {
        $this->info('Using connection: '. $this->option( 'connection' ) ."\n");
        $this->schemaGenerator = new SchemaGenerator(
            $this->option('connection'),
            $this->option('defaultIndexNames'),
            $this->option('defaultFKNames')
        );

        if ($this->option('tables')) {
            $tables = explode( ',', $this->option('tables') );
        } else {
            $tables = $this->schemaGenerator->getTables();
        }

        $tables = $this->removeExcludedTables($tables);
        $this->info('Generating migrations for: '. implode(', ', $tables));

        $schema = $this->schemaGenerator->getSchema($tables);

        $this->info("Setting up Tables and Index Migrations");
        $date = date('Y_m_d_His');
        $this->generateFromSchema('create', $schema, $date);

        $this->info("Setting up Foreign Key Migrations");
        $date = date('Y_m_d_His', strtotime('+1 second'));
        $this->generateFromSchema('foreign_keys', $schema, $date);

        $this->info("Finished!");
    }

    /**
     * Generate Migrations
     *
     * @param string $method Create Tables or Foreign Keys ['create', 'foreign_keys']
     * @param array $schema The database schema (list of Table objects)
     * @param string $date The date to be used for generating the migrations
     *
     * @throws MethodNotFoundException
     * @throws \Mayconbordin\Generator\Exceptions\FileAlreadyExistsException
     */
    protected function generateFromSchema($method, $schema, $date)
    {
        if ($method == 'create') {
            $prefix = 'create';
        } elseif ($method = 'foreign_keys') {
            $prefix = 'add_foreign_keys_to';
        } else {
            throw new MethodNotFoundException($method);
        }

        foreach ($schema as $table) {
            if ($method == 'foreign_keys' && !$table->hasForeignKeys()) continue;

            $migrationName = $prefix .'_'. $table->getName() .'_table';

            $generator = new MigrationGenerator([
                'name'             => $migrationName,
                'raw_name'         => $date .'_'. $migrationName,
                'action'           => 'create_simple',
                'force'            => $this->option('force'),
                'table'            => $table,
                'generate_foreign' => ($method == 'foreign_keys'),
                'only_foreign'     => ($method == 'foreign_keys'),
            ]);

            $generator->run();

            $this->info("Migration $migrationName created successfully.");
        }
    }

    /**
     * Remove all the tables to exclude from the array of tables
     *
     * @param $tables
     *
     * @return array
     */
    protected function removeExcludedTables($tables)
    {
        $excludes = $this->getExcludedTables();
        $tables = array_diff($tables, $excludes);
        return $tables;
    }
    /**
     * Get a list of tables to exclude
     *
     * @return array
     */
    protected function getExcludedTables()
    {
        $excludes = ['migrations'];
        $ignore = $this->option('ignore');

        if (!empty($ignore)) {
            return array_merge($excludes, explode(',', $ignore));
        }

        return $excludes;
    }
}