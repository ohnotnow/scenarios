<?php

namespace Ohffs\Scenarios;

class ScenarioStore
{
    protected $scenarios = [];
    protected $state = [];
    protected $caller;

    public function write($name, $story)
    {
        $this->scenarios[$name] = $story;
    }

    public function playout($name, $params = [])
    {
        if (!$this->caller) {
            $this->caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }
        if (!array_key_exists($name, $this->scenarios)) {
            throw new \InvalidArgumentException("Scenario {$name} not found");
        }
        $scenario = $this->scenarios[$name];
        if (is_callable($scenario)) {
            $result = call_user_func($scenario, $params);
        } else {
            $result = $scenario;
        }
        $this->state[$this->caller][] = $result;
        return $this;
    }

    public function andAlso($name, $params = [])
    {
        if (!$this->caller) {
            $this->caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }
        return $this->playout($name, $params);
    }

    public function andReturnResults()
    {
        if (count($this->state[$this->caller]) > 1) {
            $results = $this->state[$this->caller];
        } else {
            $results = $this->state[$this->caller][0];
        }
        $this->state[$this->caller] = [];
        return $results;
    }

    public function getAllResults()
    {
        return $this->state;
    }
}
