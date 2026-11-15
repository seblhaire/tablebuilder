<?php

namespace Seblhaire\TableBuilder\Classes\Columns;

use Seblhaire\TableBuilder\Classes\Columns\AbstractTableColumn;

class MailCell extends AbstractTableColumn {

    protected string $name = 'TableBuilderMailCell';
    protected string $type = 'mail';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct(string $sDataBindTo, array $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.mail'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        $sStr = $this->_options();
        if (isset($this->aOptions['copycell'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "copycell : '" . (string) $this->aOptions['copycell'] . "'";
        }
        if (isset($this->aOptions['copytext'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "copytext : '" . $this->translateOrPrint($this->aOptions['copytext']) . "'";
        }
        return "{" . $sStr . '}';
    }
}
