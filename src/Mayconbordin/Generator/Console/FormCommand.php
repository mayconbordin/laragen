<?php namespace Mayconbordin\Generator\Console;

use Illuminate\Console\Command;
use Mayconbordin\Generator\Generator\FormGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FormCommand extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'generate:form';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new form.';

    /**
     * Execute the command.
     */
    public function fire()
    {
        $generator = new FormGenerator(
            $this->argument('table'),
            $this->option('fields')
        );
        
        if ($this->option('output') == 'console') {
            $this->line($generator->render());
        } else {
            $generator->run();
            $this->info('Form created successfully.');
        }
    }

    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['table', InputArgument::OPTIONAL, 'The name of table being used.', null],
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
            ['fields', 'f', InputOption::VALUE_OPTIONAL, 'The form fields.', null],
            ['output', 'o', InputOption::VALUE_OPTIONAL, 'Where the result will be written: console (default), view.', 'console'],
        ];
    }
}
