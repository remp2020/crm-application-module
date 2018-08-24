<?php

namespace Crm\ApplicationModule\Components\Graphs;

class SmallBarGraph extends BaseGraphControl
{
    private $view = 'small_bar_graph';

    private $graphTitle = '[Názov grafu]';

    private $series = [];

    public function setGraphTitle($graphTitle)
    {
        $this->graphTitle = $graphTitle;
        return $this;
    }

    public function addSerie($data)
    {
        $this->series = $data;
        return $this;
    }

    public function render()
    {
        $this->template->graphId = $this->generateGraphId();
        $this->template->graphTitle = $this->graphTitle;
        $this->template->data = array_pop($this->series);
        if ($this->template->data == null) {
            $this->template->data = [];
        }

        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }
}
