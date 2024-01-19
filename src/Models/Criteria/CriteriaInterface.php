<?php

namespace Crm\ApplicationModule\Models\Criteria;

use Crm\SegmentModule\Models\Params\BaseParam;
use Crm\SegmentModule\Models\Params\ParamsBag;

interface CriteriaInterface
{
    /**
     * params returns an array of BaseParam definitions available for the Criteria.
     *
     * The generator and UI currently support following type of parameters:
     *
     *   * StringParam
     *   * StringArrayParam
     *   * NumberParam
     *   * NumberArrayParam
     *   * DecimalParam
     *   * BooleanParam
     *   * DateTimeParam
     *
     * @return BaseParam[]
     */
    public function params(): array;

    /**
     * label returns human-friendly and descriptive label of the whole Criteria
     *
     * @return string
     */
    public function label(): string;

    /**
     * category returns human-friendly and descriptive category of the Criteria.
     * Multiple related criteria should be placed within the same category.
     *
     * @return string
     */
    public function category(): string;

    /**
     * join returns part of SQL query on which the main query generator will be joining - usually a subquery containing
     * all the conditions based on selected parameters and provided values.
     *
     * @param ParamsBag $params
     * @return string
     */
    public function join(ParamsBag $params): string;

    /**
     * title returns part of segment name generated based on selected params and provided params values - it reflects
     * in a human-readable way selected conditions within the single Criteria.
     *
     * @param ParamsBag $params
     * @return string
     */
    public function title(ParamsBag $params): string;

    /**
     * fields returns list of fields providable by Criteria. As the join query of criteria is subquery, it may return
     * arbitrary data that might be exported with the whole segment - for example total amount spent per each user.
     *
     * @return array
     */
    public function fields(): array;
}
