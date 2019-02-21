<?php

namespace Crm\ApplicationModule\Components\Graphs;

use Crm\ApplicationModule\Graphs\GraphData;
use Nette\Application\UI\Multiplier;

class GoogleBarGraphGroup extends BaseGraphControl
{
    private $view = 'google_bar_graph_group';

    private $yLabel = '[Y label]';

    private $xLabel = '[X label]';

    private $xAxis = [];

    private $graphTitle = '[Názov grafu]';

    private $graphHelp = 'help';

    private $height = 300;

    private $series = [];

    private $seriesName = '';

    private $stacked = true;

    /** @var callable */
    private $serieTitleCallback;

    private $start = ['day' => '-60 days', 'week' => '-8 weeks', 'month' => '-4 months', 'year' => '-3 years'];

    /** @var GoogleBarGraphControlFactoryInterface */
    private $googleBarGraphFactory;

    /** @var GraphData */
    private $graphData;

    public function __construct(GoogleBarGraphControlFactoryInterface $factory, GraphData $graphData)
    {
        parent::__construct();
        $this->googleBarGraphFactory = $factory;
        $graphData->clear();
        $this->graphData = $graphData;
    }

    public function setStacked($stacked)
    {
        $this->stacked = $stacked;
        return $this;
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
        $this->template->groupBy = $this->getParameter('groupBy', 'day');

        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }

    public function addGraphDataItem($graphDataItem)
    {
        $this->graphData->addGraphDataItem($graphDataItem);
        return $this;
    }

    public function createComponentGraph()
    {
        return new Multiplier(function ($groupBy) {
            $control = $this->googleBarGraphFactory->create();
            $control->setYLabel($this->yLabel)
                ->setStacked($this->stacked)
                ->setGraphTitle($this->graphTitle);

            $this->graphData->setScaleRange($groupBy);
            if ($range = $this->getParameter('range')) {
                $this->graphData->setStart($range);
            }

            foreach ($this->graphData->getSeriesData() as $k => $v) {
                if ($this->serieTitleCallback) {
                    $k = ($this->serieTitleCallback)($k);
                }
                $control->addSerie($k, $v);
            }

            return $control;
        });
    }

    public function handleChange($range, $groupBy)
    {
        $this->template->range = $range;
        $this->template->groupBy = $groupBy;
        $this->template->redraw = true;
        $this->redrawControl('ajaxChange');
    }
}
