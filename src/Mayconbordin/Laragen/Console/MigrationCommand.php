<?php namespace Mayconbordin\Laragen\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Mayconbordin\Laragen\Console\Helpers\SchemaFieldsTrait;
use Mayconbordin\Laragen\Exceptions\FileAlreadyExistsException;
use Mayconbordin\Laragen\Exceptions\MethodNotFoundException;
use Mayconbordin\Laragen\Generator\MigrationGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use \Config;

class MigrationCommand extends Command
{
    use SchemaFieldsTrait;

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'generate:migration';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new migration.';

    /**
     * Execute the command.
     */
    public function fire(Composer $composer)
    {
        if ($this->argument('name') != null) {
            $this->generateFromCommand();
        } else {
            $this->generateFromDatabase();
        }

        $composer->dumpAutoloads();
    }

    /**
     * Generate a migration from the command line arguments.
     *
     * @throws FileAlreadyExistsException
     */
    protected function generateFromCommand()
    {
        $table = $this->fetchTableFromCli();

        $generator = new MigrationGenerator([
            'name'   => $this->argument('name'),
            'action' => $this->fetchActionFromCli(),
            'force'  => $this->option('force'),
            'table'  => $table
        ]);

        $generator->run();

        $this->info("Migration {$this->argument('name')} created successfully.");
    }

    /**
     * Generate migrations from the database.
     *
     * @throws MethodNotFoundException
     */
    protected function generateFromDatabase()
    {
        $this->info('Using connection: '. $this->option('connection') ."\n");
        $schema = $this->fetchSchemaFromDb();

        $this->info("Setting up tables and index migrations.");
        $date = date('Y_m_d_His');
        $this->generateFromSchema('create', $schema, $date);

        $this->info("Setting up foreign key migrations.");
        $date = date('Y_m_d_His', strtotime('+1 second'));
        $this->generateFromSchema('foreign_keys', $schema, $date);
    }

    /**
     * Generate Migrations
     *
     * @param string $method Create Tables or Foreign Keys ['create', 'foreign_keys']
     * @param array $schema The database schema (list of Table objects)
     * @param string $date The date to be used for generating the migrations
     *
     * @throws MethodNotFoundException
     * @throws FileAlreadyExistsException
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
                'action'           => ($method == 'create') ? 'create_simple' : 'add',
                'force'            => $this->option('force'),
                'table'            => $table,
                'generate_foreign' => ($method == 'foreign_keys'),
                'only_foreign'     => ($method == 'foreign_keys'),
            ]);

            $generator->run();

            $this->info("Migration $migrationName created successfully.");
        }
    }

    // put methods from MigrationTrait here and simplify them with the SchemaFieldsTrait

    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the migration being generated.', null]
        ];
    }

    /**
     * The array of command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The fields of migration. Separated with comma (,).', null],
            ['action', null, InputOption::VALUE_OPTIONAL, 'The name of the action: create, create_simple, add, delete or drop.', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The database connection to use.', Config::get('database.default')],
            ['tables', 't', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments'],
            ['ignore', 'i', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to ignore, separated by a comma: users,posts,comments' ],
            ['default-index-names', null, InputOption::VALUE_NONE, 'Don\'t use db index names for migrations'],
            ['default-fk-names', null, InputOption::VALUE_NONE, 'Don\'t use db foreign key names for migrations'],
        ];
    }
}
