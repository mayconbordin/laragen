<?php namespace Mayconbordin\Generator\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Mayconbordin\Generator\Database\SchemaGenerator;
use Mayconbordin\Generator\Generator\MigrationGenerator;
use Mayconbordin\Generator\Parsers\NameParser;
use Mayconbordin\Generator\Parsers\SchemaParser;
use Mayconbordin\Generator\Schema\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use \Config;

class MigrationCommand extends Command
{
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
     * @var SchemaGenerator
     */
    protected $schemaGenerator;

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

    private function generateFromCommand()
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

        $this->info('Migration created successfully.');
    }

    private function generateFromDatabase()
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
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],

            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The database connection to use.', Config::get('database.default')],
            ['tables', 't', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments'],
            ['ignore', 'i', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to ignore, separated by a comma: users,posts,comments' ],
            ['defaultIndexNames', null, InputOption::VALUE_NONE, 'Don\'t use db index names for migrations'],
            ['defaultFKNames', null, InputOption::VALUE_NONE, 'Don\'t use db foreign key names for migrations'],
        ];
    }
}
