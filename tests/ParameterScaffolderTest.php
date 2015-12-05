<?php namespace Mayconbordin\Laragen\Tests;

use Illuminate\Support\Facades\Config;
use Mayconbordin\Laragen\Scaffolders\ParameterScaffolder;

class ParameterScaffolderTest extends TestCase
{
    public function testToArray()
    {
        //Config::shouldReceive('get')->withAnyArgs()->andReturn('');

        $instance = new ParameterScaffolder('EventActivityRepository', null, 'repository');

        //$data = $instance->toArray();

        $this->assertEquals('eventactivity', $instance->getEntity());
        $this->assertEquals('eventActivities', $instance->getLowerEntities());
        $this->assertEquals('eventActivity', $instance->getLowerSingularEntity());
        $this->assertEquals('EventActivity', $instance->getStudlyEntity());
        $this->assertEquals('EventActivities', $instance->getStudlyPluralEntity());
    }
}
