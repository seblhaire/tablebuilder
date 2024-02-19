<?php

namespace Seblhaire\TableBuilder;

class DataCell extends AbstractTableColumn {

    protected $name = 'TableBuilderDataCell';
    protected $type = 'data';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct($sDataBindTo, $aOptions) {
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
