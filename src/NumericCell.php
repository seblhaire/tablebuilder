<?php
namespace Seblhaire\TableBuilder;

class NumericCell extends AbstractTableColumn
{

    protected $name = 'TableBuilderNumericCell';

    protected $type = 'numeric';

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
        $this->aOptions = array_replace(config('tablebuilder.numeric'), $aOptions);
    }

    /**
     * prints column options
     *
     * @return string
     */
    public function printOptions()
    {
        $sStr = $this->_options();
        if (isset($this->aOptions['thousandsep'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "thousandsep : '" . addslashes($this->aOptions['thousandsep']) . "'";
        }
        if (isset($this->aOptions['currency'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "currency : '" . addslashes($this->aOptions['currency']) . "'";
        }
        if (isset($this->aOptions['decimalsep'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "decimalsep : '" . addslashes($this->aOptions['decimalsep']) . "'";
        }
        if (isset($this->aOptions['decimals'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "decimals : " . addslashes($this->aOptions['decimals']);
        }
        if (isset($this->aOptions['currencyposafter'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "currencyposafter : " . ($this->aOptions['currencyposafter'] == true ? 'true' : 'false');
        }
        return '{' . $sStr . '}';
    }
}
