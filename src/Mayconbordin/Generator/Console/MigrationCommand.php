<?php namespace Mayconbordin\Generator\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Mayconbordin\Generator\Console\Helpers\MigrationTrait;
use Mayconbordin\Generator\Database\SchemaGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use \Config;

class MigrationCommand extends Command
{
    use MigrationTrait;

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
