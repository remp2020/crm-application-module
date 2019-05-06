<?php

namespace Crm\ApplicationModule\Components\Graphs;

/**
 * Google line graph component
 *
 * Component for rendering line graph using google graph library.
 *
 * @package Crm\ApplicationModule\Components\Graphs
 */
class GoogleLineGraph extends BaseGraphControl
{
    private $view = 'google_line_graph';

    private $yLabel = '[Y label]';

    private $xAxis = [];

    private $graphTitle = '[Názov grafu]';

    private $graphHelp = 'Help';

    private $height = 300;

    private $series = [];

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

    public function addSerie($name, $data)
    {
        $this->series[$name] = $data;
        if (!$this->xAxis) {
            $this->xAxis = array_keys($data);
        }
        return $this;
    }

    public function render($redraw)
    {
        $this->template->redraw = $redraw;
        $this->template->graphId = $this->generateGraphId();
        $this->template->graphTitle = $this->graphTitle;
        $this->template->graphHelp = $this->graphHelp;
        $this->template->xAxis = $this->xAxis;
        $this->template->yLabel = $this->yLabel;
        $this->template->series = $this->series;
        $this->template->height = empty($this->series) ? 0 : $this->height;

        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }
}
