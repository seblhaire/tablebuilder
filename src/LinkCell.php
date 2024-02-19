<?php

namespace Seblhaire\TableBuilder;

class LinkCell extends AbstractTableColumn {

    protected $name = 'TableBuilderLinkCell';
    protected $type = 'link';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct($sDataBindTo, $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.link'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        $sStr = $this->_options();
        if (isset($this->aOptions['shorten'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "shorten : '" . (string) $this->aOptions['shorten'] . "'";
        }
        if (isset($this->aOptions['maxlength'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "maxlength : '" . (int) $this->aOptions['maxlength'] . "'";
        }
        if (isset($this->aOptions['target'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "target : '" . (int) $this->aOptions['target'] . "'";
        }
        return "{" . $sStr . '}';
    }
}
