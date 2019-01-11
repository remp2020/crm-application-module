<?php

namespace Crm\ApplicationModule\Components\Graphs;

use Crm\ApplicationModule\Graphs\GraphData;
use Crm\ApplicationModule\Graphs\ScaleFactory;

class GoogleLineGraphGroup extends BaseGraphControl
{
    private $view = 'google_line_graph_group';

    private $yLabel = '[Y label]';

    private $xLabel = '[X label]';

    private $xAxis = [];

    private $graphTitle = '[Názov grafu]';

    private $graphHelp = 'help';

    private $height = 300;

    private $series = [];

    private $seriesName = '';

    /** @var callable */
    private $serieTitleCallback;

    private $start = ['day' => '-60 days', 'week' => '-8 weeks', 'month' => '-4 months', 'year' => '-3 years'];

    /** @var GoogleLineGraphControlFactoryInterface */
    private $googleLineGraphFactory;

    /** @var GraphData */
    private $graphData;

    private $scaleFactory;

    public function __construct(GoogleLineGraphControlFactoryInterface $factory, GraphData $graphData, ScaleFactory $scaleFactory)
    {
        parent::__construct();
        $this->googleLineGraphFactory = $factory;
        $graphData->clear();
        $this->graphData = $graphData;
        $this->scaleFactory = $scaleFactory;
    }

    public function setYLabel($yLabel)
    {
        $this->yLabel = $yLabel;
        return $this;
    }

    public function setXLabel($xLabel)
    {
        $this->xLabel = $xLabel;
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

    public function setSerieTitleCallback($serieTitleCallback)
    {
        $this->serieTitleCallback = $serieTitleCallback;
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

    public function setSeries($seriesName)
    {
        $this->seriesName = $seriesName;
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
        $this->template->range = $this->getParameter('range', $this->start['day']);

        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }

    public function addGraphDataItem($graphDataItem)
    {
        $this->graphData->addGraphDataItem($graphDataItem);
        return $this;
    }

    protected function createComponentDaysGraph()
    {
        $control = $this->googleLineGraphFactory->create();
        $control->setYLabel($this->yLabel)
            ->setGraphTitle($this->graphTitle);

        $this->graphData->setScaleRange('day');
        if ($range = $this->getParameter('range')) {
            $this->graphData->setStart($range);
        }

        foreach ($this->graphData->getSeriesData() as $k => $v) {
            if (empty($v)) {
                continue;
            }
            if ($this->serieTitleCallback) {
                $k = ($this->serieTitleCallback)($k);
            }
            $control->addSerie($k, $v);
        }

        return $control;
    }

    protected function createComponentWeeksGraph()
    {
        $control = $this->googleLineGraphFactory->create();
        $control->setYLabel($this->yLabel)
            ->setGraphTitle($this->graphTitle);

        $this->graphData->setScaleRange('week')
            ->setStart($this->getParameter('range', $this->start['week']));

        foreach ($this->graphData->getSeriesData() as $k => $v) {
            $control->addSerie($k, $v);
        }

        return $control;
    }

    protected function createComponentMonthsGraph()
    {
        $control = $this->googleLineGraphFactory->create();
        $control->setYLabel($this->yLabel)
            ->setGraphTitle($this->graphTitle);

        $this->graphData->setScaleRange('month')
            ->setStart($this->getParameter('range', $this->start['month']));

        foreach ($this->graphData->getSeriesData() as $k => $v) {
            $control->addSerie($k, $v);
        }

        return $control;
    }

    protected function createComponentYearsGraph()
    {
        $control = $this->googleLineGraphFactory->create();
        $control->setYLabel($this->yLabel)
            ->setGraphTitle($this->graphTitle);

        $this->graphData->setScaleRange('year')
            ->setStart($this->getParameter('range', $this->start['year']));

        foreach ($this->graphData->getSeriesData() as $k => $v) {
            $control->addSerie($k, $v);
        }

        return $control;
    }

    public function handleRangeSwitch($range)
    {
        $this->template->range = $range;
        $this->template->redraw = true;
        $this->redrawControl('ajaxChange');
    }
}
