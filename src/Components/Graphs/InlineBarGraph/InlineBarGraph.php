<?php

namespace Crm\ApplicationModule\Components\Graphs;

/**
 * Inline bar graph
 *
 * Component for rendering very simple inline bar graphs.
 *
 * @package Crm\ApplicationModule\Components\Graphs
 */
class InlineBarGraph extends BaseGraphControl
{
    private $view = 'inline_bar_graph';

    private $yLabel = '[Y label]';

    private $xAxis = [];

    private $graphTitle = '[Názov grafu]';

    private $graphHelp = 'help';

    private $height = 300;

    public function setYLabel($ylabel)
    {
        $this->yLabel = $ylabel;
        return $this;
    }

    public function setGraphTitle($graphTitle)
    {
        $this->graphTitle = $graphTitle;
        return $this;
    }

    public function setGraphHelp($graphHelp)
    {
        $this->graphHelp = $graphHelp;
        return $this;
    }

    public function addSerie($data)
    {
        $this->series['data'] = $data;

        if (!$this->xAxis) {
            $this->xAxis = array_keys($data);
        }
        return $this;
    }

    public function render()
    {
        $this->template->graphId = $this->generateGraphId();
        $this->template->graphTitle = $this->graphTitle;
        $this->template->graphHelp = $this->graphHelp;
        $this->template->xAxis = $this->xAxis;
        $this->template->yLabel = $this->yLabel;
        $this->template->series = $this->series;
        $this->template->height = $this->height;

        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }
}
