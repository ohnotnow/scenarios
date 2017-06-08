<?php

use PHPUnit\Framework\TestCase;
use Ohffs\Scenarios\ScenarioStore;
use Ohffs\Scenarios\HasScenarios;

class ScenarioTest extends TestCase
{
    use HasScenarios;

    /** @test */
    public function it_can_store_and_retrieve_a_plain_scenario()
    {
        $scenarios = new ScenarioStore;

        $scenarios->write('a_test_thing', ['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $scenarios->playout('a_test_thing')->andReturnResults());
    }

    /** @test */
    public function it_can_store_and_retrieve_a_callable_scenario()
    {
        $scenarios = new ScenarioStore;

        $scenarios->write('a_test_thing', function () {
            return ['a', 'b', 'c'];
        });

        $this->assertEquals(['a', 'b', 'c'], $scenarios->playout('a_test_thing')->andReturnResults());
    }

    /** @test */
    public function retrieving_a_nonexistant_scenario_raises_an_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $scenarios = new ScenarioStore;

        $scenarios->playout('nonexistant_thing');
    }

    /** @test */
    public function can_use_the_scenario_trait()
    {
        $this->scenarios()->write('a_test_thing', ['a', 'b', 'c']);

        $this->assertEquals(['a', 'b', 'c'], $this->scenarios()->playout('a_test_thing')->andReturnResults());
    }

    /** @test */
    public function it_can_run_scenarios_fluently()
    {
        $this->scenarios()->write('we have an array', ['a', 'b', 'c']);
        $this->scenarios()->write('we have an integer', 10);

        list($array, $integer) = $this->scenarios()
                                    ->playout('we have an array')
                                    ->andAlso('we have an integer')
                                    ->andReturnResults();

        $this->assertEquals(['a', 'b', 'c'], $array);
        $this->assertEquals(10, $integer);
    }

    /** @test */
    public function a_scenario_allows_parameters_to_be_passed()
    {
        $this->scenarios()->write('we have a user', function ($params) {
            return array_merge(['username' => 'jenny', 'email' => 'jenny@example.com'], $params);
        });

        $user = $this->scenarios()->playout('we have a user', ['email' => 'jenny@notexample.com'])->andReturnResults();

        $this->assertEquals(['username' => 'jenny', 'email' => 'jenny@notexample.com'], $user);
    }
}
