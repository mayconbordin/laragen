<?php namespace Mayconbordin\Laragen\Console;

use Illuminate\Console\Command;
use Mayconbordin\Laragen\Generator\FormGenerator;
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
        $generator = new FormGenerator([
            'name'   => $this->argument('table'),
            'table'  => $this->argument('table'),
            'fields' => $this->option('fields')
        ]);
        
        if ($this->option('output') == 'console') {
            $this->line($generator->render());
        } else {
            if (!$this->argument('table')) {
                throw new \RuntimeException("The table argument is required");
            }
        
            $generator->run();
            $this->info("Form {$generator->getClass()} created successfully.");
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
