<?php namespace Mayconbordin\Generator\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Mayconbordin\Generator\Generator\MigrationGenerator;
use Mayconbordin\Generator\Parsers\NameParser;
use Mayconbordin\Generator\Parsers\SchemaParser;
use Mayconbordin\Generator\Schema\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
     * Execute the command.
     */
    public function fire(Composer $composer)
    {
        $meta = (new NameParser())->parse($this->argument('name'));
        $table = $this->buildTableFromCommand($meta['table']);

        $generator = new MigrationGenerator([
            'name'   => $this->argument('name'),
            'action' => $meta['action'],
            'force'  => $this->option('force'),
            'table'  => $table
        ]);

        $generator->run();

        $this->info('Migration created successfully.');

        $composer->dumpAutoloads();
    }

    private function buildTableFromCommand($tableName)
    {
        $fields = (new SchemaParser())->parse($this->option('fields'));
        return new Table($tableName, $fields);
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
            ['fields', 'c', InputOption::VALUE_OPTIONAL, 'The fields of migration. Separated with comma (,).', null],
            ['force', 'f', InputOption::VALUE_NONE, 'Force the creation if file already exists.', null],
        ];
    }
}
