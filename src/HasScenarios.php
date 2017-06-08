<?php

namespace Ohffs\Scenarios;

trait HasScenarios
{
    protected $scenarioStore;

    public function scenarios()
    {
        if (!$this->scenarioStore) {
            $this->scenarioStore = new ScenarioStore;
        }
        return $this->scenarioStore;
    }
}
