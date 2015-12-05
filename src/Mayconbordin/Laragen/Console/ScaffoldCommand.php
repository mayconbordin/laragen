<?php namespace Mayconbordin\Laragen\Console;

use Illuminate\Console\Command;
use Mayconbordin\Laragen\Console\Helpers\SchemaFieldsTrait;
use Mayconbordin\Laragen\Generator\ScaffoldGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use \Config;

class ScaffoldCommand extends Command
{
    use SchemaFieldsTrait;

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'generate:scaffold';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new scaffold resource.';

    /**
     * Execute the command.
     */
    public function fire()
    {
        $schema = [];

        if ($this->argument('name')) {
            $schema[] = $this->fetchTableFromCli();
        } else {
            $this->info('Using connection: '. $this->option('connection') ."\n");
            $schema = $this->fetchSchemaFromDb();
        }

        $generator = new ScaffoldGenerator($this, $schema);
        $generator->run();

        $this->info("All done.");
    }

    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The entity name.', null],
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
            ['prefix', null, InputOption::VALUE_OPTIONAL, 'The prefix path & routes.', null],

            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'The database connection to use.', Config::get('database.default')],
            ['tables', 't', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments'],
            ['ignore', 'i', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to ignore, separated by a comma: users,posts,comments' ],
            ['defaultIndexNames', null, InputOption::VALUE_NONE, 'Don\'t use db index names for migrations'],
            ['defaultFKNames', null, InputOption::VALUE_NONE, 'Don\'t use db foreign key names for migrations'],

            ['languages', 'l', InputOption::VALUE_OPTIONAL, 'The list of languages (comma-separated) in which the resource will be created. Default: en.', 'en'],

            ['no-question', null, InputOption::VALUE_NONE, 'Don\'t ask any question.', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
            ['repository', null, InputOption::VALUE_NONE, 'Generate the repository classes and controllers that use the repositories.', null],
        ];
    }
}
