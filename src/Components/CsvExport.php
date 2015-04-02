<?php

namespace Nayjest\Grids\Components;

use App;
use Event;
use Illuminate\Http\Response;
use Nayjest\Grids\Components\Base\RenderableComponent;
use Nayjest\Grids\Components\Base\RenderableRegistry;
use Nayjest\Grids\DataProvider;
use Nayjest\Grids\DataRow;
use Nayjest\Grids\Grid;

/**
 * @author: Vitaliy Ofat <i@vitaliy-ofat.com>
 */
class CsvExport extends RenderableComponent
{
    const NAME = 'csv_export';
    const INPUT_PARAM = 'csv';
    const CSV_DELIMITER = ';';
    const CSV_EXT = '.csv';
    const DEFAULT_ROWS_LIMIT = 5000;

    protected $template = '*.components.csv_export';
    protected $name = CsvExport::NAME;
    protected $render_section = RenderableRegistry::SECTION_END;
    protected $rows_limit = self::DEFAULT_ROWS_LIMIT;

    /**
     * @var string
     */
    protected $output;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @param Grid $grid
     * @return null|void
     */
    public function initialize(Grid $grid)
    {
        parent::initialize($grid);
        Event::listen(Grid::EVENT_PREPARE, function (Grid $grid) {
            $this->grid = $grid;
            if ($grid->getInputProcessor()->getValue(static::INPUT_PARAM, false)) {
                $this->renderCsv();
            }
        });
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setFileName($name)
    {
        $this->fileName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName . static::CSV_EXT;
    }

    /**
     * @return int
     */
    public function getRowsLimit()
    {
        return $this->rows_limit;
    }

    /**
     * @param int $rows_limit
     *
     * @return $this
     */
    public function setRowsLimit($rows_limit)
    {
        $this->rows_limit = $rows_limit;
        return $this;
    }

    protected function setCsvHeaders(Response $response)
    {
        $response->header('Content-Type', 'application/csv');
        $response->header('Content-Disposition', 'attachment; filename=' . $this->getFileName());
        $response->header('Pragma', 'no-cache');
    }

    protected function resetPagination(DataProvider $provider)
    {
        $provider->setPageSize($this->getRowsLimit());
        $provider->setCurrentPage(1);
    }

    protected function renderCsv()
    {
        $f = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'. $this->getFileName() .'"');
        header("Pragma: no-cache");

        set_time_limit(0);

        /** @var $provider DataProvider */
        $provider = $this->grid->getConfig()->getDataProvider();

        $this->renderHeader($f);

        $this->resetPagination($provider);
        $provider->reset();
        /** @var DataRow $row */
        while ($row = $provider->getRow()) {
            $output = [];
            foreach ($this->grid->getConfig()->getColumns() as $column) {
                if (!$column->isHidden()) {
                    $output[] = $this->escapeString( $column->getValue($row) );
                }
            }
            fputcsv($f, $output, static::CSV_DELIMITER);

        }

        fclose($f);
        exit;
    }

    protected function escapeString($str)
    {
        return str_replace('"', '\'', strip_tags(html_entity_decode($str)));
    }

    protected function renderHeader($f)
    {
        $output = [];
        foreach ($this->grid->getConfig()->getColumns() as $column) {
            if (!$column->isHidden()) {
                $output[] = $this->escapeString($column->getLabel());
            }
        }
        fputcsv($f, $output, static::CSV_DELIMITER);
    }

}