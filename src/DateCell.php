<?php

namespace Seblhaire\TableBuilder;

class DateCell extends AbstractTableColumn {

    protected $name = 'TableBuilderDateCell';
    protected $type = 'date';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct($sDataBindTo, $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.date'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        $sStr = $this->_options();
        if (isset($this->aOptions['format'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "format : '" . $this->aOptions['format'] . "'";
        }
        return '{' . $sStr . '}';
    }
}
