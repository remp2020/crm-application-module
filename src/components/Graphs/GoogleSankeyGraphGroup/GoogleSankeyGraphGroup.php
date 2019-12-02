<?php

namespace Crm\ApplicationModule\Components\Graphs;

use Nette\Utils\Json;

class GoogleSankeyGraphGroup extends BaseGraphControl
{
    private $view = 'google_sankey_graph_group';

    private $graphTitle = '';

    private $graphHelp = '';

    private $height = 300;

    private $firstColumnName = '';

    private $secondColumnName = '';

    private $countColumnName = '';

    private $rows = [];

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

    public function setColumnNames($first, $second, $count)
    {
        $this->firstColumnName = $first;
        $this->secondColumnName = $second;
        $this->countColumnName = $count;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function render()
    {
        $this->template->graphId = $this->generateGraphId();
        $this->template->graphTitle = $this->graphTitle;
        $this->template->graphHelp = $this->graphHelp;
        $this->template->height = $this->height;

        $this->template->firstColumnName = $this->firstColumnName;
        $this->template->secondColumnName = $this->secondColumnName;
        $this->template->countColumnName = $this->countColumnName;

        $this->template->empty = empty($this->rows);
        $this->template->rows = Json::encode($this->rows);

        $this->template->setFile(__DIR__ . '/' . $this->view . '.latte');
        $this->template->render();
    }
}
