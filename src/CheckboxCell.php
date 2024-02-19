<?php

namespace Seblhaire\TableBuilder;

class CheckboxCell extends AbstractTableColumn {

    protected $name = 'TableBuilderCheckboxCell';
    protected $type = 'checkbox';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct($sDataBindTo, $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.checkbox'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        $sStr = $this->_options();
        if (isset($this->aOptions['action'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= 'action : ' . $this->aOptions['action'];
        }
        if (isset($this->aOptions['isEnabledCallback'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= 'isEnabledCallback : ' . $this->aOptions['isEnabledCallback'];
        }
        return '{' . $sStr . '}';
    }
}
