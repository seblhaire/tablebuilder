<?php

namespace Seblhaire\TableBuilder\Classes\Columns;

use Seblhaire\TableBuilder\Classes\Columns\AbstractTableColumn;

class ImageCell extends AbstractTableColumn {

    protected string $name = 'TableBuilderImageCell';
    protected string $type = 'image';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct(string $sDataBindTo, array $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.image'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        $sStr = $this->_options();
        if (isset($this->aOptions['tag'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "tag : '" . $this->aOptions['tag'] . "'";
        }
        if (isset($this->aOptions['imgclass'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "imgclass : '" . $this->aOptions['imgclass'] . "'";
        }

        return "{" . $sStr . "}";
    }
}
