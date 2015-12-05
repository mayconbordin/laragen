<?php namespace Mayconbordin\Generator\Console;

use Illuminate\Console\Command;
use Mayconbordin\Generator\Generator\ModelGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelCommand extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'generate:model';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new model.';

    /**
     * Execute the command.
     */
    public function fire()
    {
        $generator = new ModelGenerator([
            'name'     => $this->argument('name'),
            'fillable' => $this->option('fillable'),
            'fields'   => $this->option('fields'),
            'force'    => $this->option('force'),
        ]);

        $generator->run();

        $this->info("Model {$generator->getClass()} created successfully.");
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
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes.', null],
            ['fields', null, InputOption::VALUE_OPTIONAL, 'The fields of migration. Separated with comma (,).', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
        ];
    }
}
