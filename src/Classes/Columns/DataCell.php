<?php

namespace Seblhaire\TableBuilder\Classes\Columns;

use Seblhaire\TableBuilder\Classes\Columns\AbstractTableColumn;

class DataCell extends AbstractTableColumn {

    protected string $name = 'TableBuilderDataCell';
    protected string $type = 'data';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct(string $sDataBindTo, array $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.data'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        return "{" . $this->_options() . "}";
    }
}
