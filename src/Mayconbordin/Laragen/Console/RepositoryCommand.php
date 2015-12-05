<?php namespace Mayconbordin\Laragen\Console;

use Illuminate\Console\Command;
use Mayconbordin\Laragen\Generator\RepositoryGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RepositoryCommand extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'generate:repository';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new repository.';

    /**
     * Execute the command.
     */
    public function fire()
    {
        $generator = new RepositoryGenerator([
            'name'     => $this->argument('name'),
            'force'    => $this->option('force'),
        ]);

        $generator->run();

        $this->info("Repository {$generator->getClass()} created successfully.");
    }

    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of class being generated.', null],
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
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
        ];
    }
}
