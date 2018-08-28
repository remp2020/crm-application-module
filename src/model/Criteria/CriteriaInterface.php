<?php

namespace Crm\ApplicationModule\Criteria;

use Crm\SegmentModule\Params\BaseParam;
use Crm\SegmentModule\Params\ParamsBag;

interface CriteriaInterface
{
    /**
     * @return BaseParam[]
     */
    public function params(): array;

    public function label(): string;

    public function category(): string;

    public function join(ParamsBag $params): string;

    public function title(ParamsBag $params): string;

    public function fields(): array;
}
