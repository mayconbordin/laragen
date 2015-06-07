<?php namespace Mayconbordin\Generator;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * The array of consoles.
     *
     * @var array
     */
    protected $consoles = [
        'Model',
        'Controller',
        'Console',
        'View',
        'Seed',
        'Migration',
        'Request',
        'Pivot',
        'Scaffold',
        'Form',
    ];
    
    /**
     * Register the service provider.
     */
    public function register()
    {
        foreach ($this->consoles as $console) {
            $this->commands('Mayconbordin\Generator\Console\\'.$console.'Command');
        }
    }
}
