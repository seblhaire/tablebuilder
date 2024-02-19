<?php

namespace Seblhaire\TableBuilder;

/**
 * Action cell: buttons that trigger actions
 */
class ActionCell extends AbstractTableColumn {

    protected $name = 'TableBuilderActionCell';
    protected $type = 'action';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct($sDataBindTo, $aOptions) {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.action'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions() {
        $sStr = $this->_options();
        if (isset($this->aOptions['actions']) && count($this->aOptions['actions']) > 0) {
            $sStr .= ',actions : [';
            $sStr2 = '';
            foreach ($this->aOptions['actions'] as $aAction) {
                if (strlen($sStr2) > 0)
                    $sStr2 .= ',';
                $sStr3 = '';
                if (isset($aAction['img'])) {
                    $sStr3 .= "img : '" . $aAction['img'] . "'";
                } else if (isset($aAction['em'])) {
                    $sStr3 .= "em : '" . $aAction['em'] . "'";
                }
                if (isset($aAction['text'])) {
                    if (strlen($sStr3) > 0)
                        $sStr3 .= ',';
                    $sStr3 .= "text : '" . addslashes($aAction['text']) . "'";
                }
                if (isset($aAction['js'])) {
                    if (strlen($sStr3) > 0)
                        $sStr3 .= ',';
                    $sStr3 .= "js : " . $aAction['js'];
                }
                $sStr2 .= '{' . $sStr3 . '}';
            }
            $sStr .= $sStr2 . ']';
        }
        return "{" . $sStr . "}";
    }
}
