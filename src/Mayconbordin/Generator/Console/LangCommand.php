<?php namespace Mayconbordin\Generator\Console;

use Illuminate\Console\Command;
use Mayconbordin\Generator\Generator\LangGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class LangCommand extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'generate:lang';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Generate a new language resource.';

    /**
     * Execute the command.
     */
    public function fire()
    {
        $languages = preg_split('/,\s?(?![^()]*\))/', $this->option('languages'));

        foreach ($languages as $language) {
            $generator = new LangGenerator([
                'name'         => $this->argument('name'),
                'language'     => $language,
                'translations' => $this->option('translations'),
                'force'        => $this->option('force'),
            ]);

            $generator->run();

            $this->info("Language resource {$generator->getFileName()} created successfully.");
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
            ['name', InputArgument::REQUIRED, 'The name of language resource being generated.', null],
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
            ['languages', 'l', InputOption::VALUE_OPTIONAL, 'The list of languages (comma-separated) in which the resource will be created. Default: en.', 'en'],
            ['translations', 't', InputOption::VALUE_OPTIONAL, 'List of translations to be included in the resource file. Example: "test1=\'test one\', test2=\'teste two\'"', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
        ];
    }
}
