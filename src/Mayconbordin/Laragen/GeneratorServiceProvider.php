<?php namespace Mayconbordin\Laragen;

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
        'Repository',
        'Lang'
    ];
    
    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../resources/config/generator.php' => config_path('generator.php')
        ]);
        
        $this->mergeConfigFrom(
            __DIR__ . '/../../resources/config/generator.php', 'generator'
        );
        
        //$this->loadTranslationsFrom(__DIR__ . '/../../../resources/lang', 'repository');
    }
    
    /**
     * Register the service provider.
     */
    public function register()
    {
        foreach ($this->consoles as $console) {
            $this->commands('Mayconbordin\Laragen\Console\\'.$console.'Command');
        }
    }
}
