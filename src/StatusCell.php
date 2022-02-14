<?php
namespace Seblhaire\TableBuilder;

class StatusCell extends AbstractTableColumn
{

    protected $name = 'TableBuilderStatusCell';

    protected $type = 'status';

    /**
     * constructor
     *
     * @param string $sDataBindTo
     *            field name that contains column content
     * @param array $aOptions
     */
    public function __construct($sDataBindTo, $aOptions)
    {
        $this->dataBindTo = $sDataBindTo;
        $this->aOptions = array_replace(config('tablebuilder.status'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions()
    {
        $sStr = $this->_options();
        if (isset($this->aOptions['aIcons']) && count($this->aOptions['aIcons']) > 0) {
            $sStr .= ',aIcons : {';
            $sStr2 = '';
            foreach ($this->aOptions['aIcons'] as $key => $aAction) {
                if (strlen($sStr2) > 0)
                    $sStr2 .= ',';
                $sStr3 = '';
                if (isset($aAction['style'])) {
                    $sStr3 .= "style : '" . $aAction['style'] . "'";
                }
                if (isset($aAction['title'])) {
                    if (strlen($sStr3) > 0)
                        $sStr3 .= ',';
                    $sStr3 .= "title : '" . addslashes($aAction['title']) . "'";
                }
                if (isset($aAction['class'])) {
                    if (strlen($sStr3) > 0)
                        $sStr3 .= ',';
                    $sStr3 .= "class : '" . addslashes($aAction['class']) . "'";
                }
                $sStr2 .= '"' . $key . '":{' . $sStr3 . '}';
            }
            $sStr .= $sStr2 . '}';
        }
        return "{" . $sStr . "}";
    }
}
