<?php

namespace TheNandan\Grids;


use Illuminate\Support\Facades\View;
use TheNandan\Grids\Grid as NayGrid;
use TheNandan\Grids\Components\HtmlTag;
use TheNandan\Grids\Components\CsvExport;
use TheNandan\Grids\Components\ColumnsHider;
use TheNandan\Grids\Components\ColumnHeadersRow;
use TheNandan\Grids\Components\Base\RenderableRegistry;
use TheNandan\Grids\Components\ExcelExport;
use TheNandan\Grids\Components\FiltersRow;
use TheNandan\Grids\Components\Laravel5\Pager;
use TheNandan\Grids\Components\OneCellRow;
use TheNandan\Grids\Components\RecordsPerPage;
use TheNandan\Grids\Components\RenderFunc;
use TheNandan\Grids\Components\ShowingRecords;
use TheNandan\Grids\Components\TFoot;
use TheNandan\Grids\Components\THead;
use TheNandan\Grids\Helpers\Row;
use TheNandan\Grids\Helpers\Column;
use Illuminate\Support\Facades\Gate;
use Collective\Html\HtmlFacade as HTML;

/**
 * Class LaravelGrid
 *
 * @package TheNandan\Grids
 */
class TheNandanGrid
{
    public const OPERATOR_LIKE = 'like';
    public const OPERATOR_EQ = '=';
    public const OPERATOR_NOT_EQ = '<>';
    public const OPERATOR_GT = '>';
    public const OPERATOR_LS = '<';
    public const OPERATOR_LSE = '<=';
    public const OPERATOR_GTE = '>=';

    public const SORT_ASC = 'ASC';
    public const SORT_DESC = 'DESC';

    private $gridConfig;
    private $customHeaderRow;
    private $hasDateRangePicker = false;
    private $hiddenColumns = [];
    private $exportPermission = null;

    /**
     * @param $source
     */
    public function setGridConfig($source): void
    {
        $this->gridConfig = new GridConfig($source);
    }

    /**
     * Set the default page size
     *
     * @param $number
     */
    public function setDefaultPageSize($number): void
    {
        $this->gridConfig->setPageSize($number);
    }

    /**
     * This method can be used to set the grid name
     *
     * @param $name
     */
    public function setGridName($name): void
    {
        $this->gridConfig->setName($name);
    }

    /**
     * This method can be used to set the caching time in minute
     *
     * @param $timeInMinute
     */
    public function setCachingTime($timeInMinute): void
    {
        $this->gridConfig->setCachingTime($timeInMinute);
    }

    /**
     * This method can be used to set column of grid
     *
     * @param $column
     * @param false $label
     * @param false $relation
     *
     * @return Column
     */
    public function addColumn($column, $label = false, $relation = false)
    {
        $column = new Column($column, $label, $relation);
        $column->setGrid($this);
        $this->gridConfig->addColumn($column->getColumn());
        return $column;
    }

    /**
     * @param $datePicker
     * @param $name
     */
    public function setDateRangePicker($datePicker, $name)
    {
        $filtersRow = $this->gridConfig->getComponentByNameRecursive(FiltersRow::NAME);
        $filtersRow->addComponent($datePicker);
        if (!$this->hasDateRangePicker) {
            $renderAssets = (new RenderFunc(function () {
                return HTML::style(asset('grid/datepicker.css'))
                    .HTML::script(asset('grid/moment.min.js'))
                    .HTML::script(asset('grid/datepicker.min.js'));
            }))
                ->setRenderSection('filters_row_column_'.$name);
            $filtersRow->addComponent($renderAssets);
        }
        $this->hasDateRangePicker = true;

    }


    /**
     * @return View|string
     */
    public function render()
    {
        $grid = new NayGrid($this->gridConfig);
        return $grid->render();
    }
}
