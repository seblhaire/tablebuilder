<?php
namespace Seblhaire\TableBuilder;

class ImageCell extends AbstractTableColumn
{

    protected $name = 'TableBuilderImageCell';

    protected $type = 'image';

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
        $this->aOptions = array_replace(config('tablebuilder.image'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions()
    {
        $sStr = $this->_options();
        if (isset($this->aOptions['tag'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "tag : '" . $this->aOptions['tag'] . "'";
        }

        return "{" . $sStr . "}";
    }
}
