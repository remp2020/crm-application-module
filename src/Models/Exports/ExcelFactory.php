<?php

namespace Crm\ApplicationModule\Models\Exports;

use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelFactory
{
    /** @var ApplicationConfig  */
    private $applicationConfig;

    public function __construct(ApplicationConfig $applicationConfig)
    {
        $this->applicationConfig = $applicationConfig;
    }

    /**
     * @param $documentTitle
     * @return Spreadsheet
     */
    public function createExcel($documentTitle)
    {
        $name = $this->applicationConfig->get('site_title');
        $excel = new Spreadsheet();
        $excel->getProperties()
            ->setCreator($name)
            ->setLastModifiedBy($name)
            ->setTitle($documentTitle);
        return $excel;
    }
}
