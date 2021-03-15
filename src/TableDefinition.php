<?php

namespace Seblhaire\TableBuilder;

/**
 * class that sets table contents and prints Javascript code needed by table object
 */
class TableDefinition
{

    private $sTableElement = null;

    private $url = null;

    private $aOptions = array();

    private $aColumns = null;

    /**
     * constructor
     *
     * @param string $tableElement
     *            table id
     * @param string $url
     *            route to get table data
     * @param array $options
     *            table options
     */
    public function __construct($tableElement, $url, $options = [])
    {
        $this->sTableElement = $tableElement;
        $this->url = $url;
        $this->aOptions = array_merge(array_replace(config('tablebuilder.table'), $options), [
            'csrf' => csrf_token()
        ]);
        $this->aColumns = array();
    }

    /**
     * outputs table variable name
     *
     * @return string vaiiable name
     */
    public function tablevar()
    {
        return $this->sTableElement . "_table";
    }

    /**
     * adds a column object to table
     *
     * @param AbstractTableColumn $obj
     *            table column
     */
    public function addColumn($obj)
    {
        if (is_a($obj, '\Seblhaire\TableBuilder\AbstractTableColumn')) {
            $this->aColumns[] = $obj;
        } else {
            throw new \Exception('wrong obj');
        }
    }

    /**
     * print js instruction to reload table data
     *
     * @return string js instruction
     */
    public function outputReload()
    {
        return "jQuery('#" . $this->sTableElement . "').data('mytable').reload();";
    }

    /**
     * Outputs table definition
     *
     * @return string html + js code
     */
    public function output()
    {
        if (count($this->aColumns) == 0) {
            throw new \Exception('empty cols');
        }
        $sStr = "<div id=\"" . $this->sTableElement . "\"></div>\n";
        $sStr .= '<script type="text/javascript">' . "\n";
        // $sStr .= "let " . $this->tablevar() . " = null\n";
        $sStr .= "$(document).ready(function() {\n";
        // $sStr .= $this->tablevar() . " = new MyTable('" . $this->sTableElement . "',{\n";
        $cols = '';
        foreach ($this->aColumns as $oColumn) {
            if ($cols != '')
                $cols .= ",\n";
            $cols .= "{type:'" . $oColumn->type() . "', data:" . $oColumn->dataBindTo() . ", options: " . $oColumn->printOptions() . "}";
        }
        $sStr .= "jQuery('#" . $this->sTableElement . "').tablebuilder('" . $this->url . "',[\n" . $cols . "\n]";
        $sStr .= ",{\nsearchable : " . ($this->aOptions['searchable'] == true ? 'true' : 'false');
        $sStr .= ",\ncsrf : \"" . $this->aOptions['csrf'];
        $sStr .= "\",\nitemsperpage : " . $this->aOptions['itemsperpage'];
        $sStr .= ",\npagechoices : [" . implode(",", $this->aOptions['pagechoices']) . "]";
        $sStr .= ",\nencoding : '" . $this->aOptions['encoding'] . "'";
        if (isset($this->aOptions['paramsFunction'])) {
            $sStr .= ",\nparamsFunction : " . $this->aOptions['paramsFunction'];
        }
        $p = '';
        if (isset($this->aOptions['additionalparams']) && count($this->aOptions['additionalparams']) > 0) {

            foreach ($this->aOptions['additionalparams'] as $sParam => $vValue) {
                if (strlen($p) > 0)
                    $p .= ",";
                $p .= "'" . $sParam . "' : '" . $vValue . "'";
            }
        }
        $sStr .= ",\nadditionalParams : {" . $p . '}';
        $sStr .= ",\ntableClass : '" . $this->aOptions['tableClass'] . "'";
        $sStr .= ",\nheadersclass : '" . $this->aOptions['headersclass'] . "'";
        $sStr .= ",\nsearchdivclass : '" . $this->aOptions['searchdivclass'] . "'";
        $sStr .= ",\nsearchinputgrpclass : '" . $this->aOptions['searchinputgrpclass'] . "'";
        $sStr .= ",\nsearchresetbuttonclass : '" . $this->aOptions['searchresetbuttonclass'] . "'";
        $sStr .= ",\nsearchresetbuttondivclass : '" . $this->aOptions['searchresetbuttondivclass'] . "'";
        $sStr .= ",\neltspageclass : '" . $this->aOptions['eltspageclass'] . "'";
        $sStr .= ",\npaginationclass : '" . $this->aOptions['paginationclass'] . "'";
        $sStr .= ",\npagecontclass : '" . $this->aOptions['pagecontclass'] . "'";
        $sStr .= ",\nbottomclass : '" . $this->aOptions['bottomclass'] . "'";
        $sStr .= ",\nfooterclass : '" . $this->aOptions['footerclass'] . "'";
        $sStr .= ",\nbuttonclass : '" . $this->aOptions['buttonclass'] . "'";
        $sStr .= ",\nrowcontextualtrigger : '" . $this->aOptions['rowcontextualtrigger'] . "'";
        $sStr .= ",\najaximgname : '" . $this->aOptions['ajaximgname'] . "'";
        $sStr .= ",\nuparrow : '" . $this->aOptions['uparrow'] . "'";
        $sStr .= ",\ndownarrow : '" . $this->aOptions['downarrow'] . "'";
        $sStr .= ",\nleftarrow : '" . $this->aOptions['leftarrow'] . "'";
        $sStr .= ",\ndblleftarrow : '" . $this->aOptions['dblleftarrow'] . "'";
        $sStr .= ",\nrightarrow: '" . $this->aOptions['rightarrow'] . "'";
        $sStr .= ",\ndblrightarrow : '" . $this->aOptions['dblrightarrow'] . "'";
        $sStr .= ",\nbuttondivarrow : '" . $this->aOptions['buttondivarrow'] . "'";
        $sStr .= ",\najaxerrormsg : '" . $this->translateOrPrint($this->aOptions['ajaxerrormsg']) . "'";
        $sStr .= ",\nnodatastr : '" . $this->translateOrPrint($this->aOptions['nodatastr']) . "'";
        $sStr .= ",\nchkheadlabel : '" . $this->translateOrPrint($this->aOptions['chkheadlabel']) . "'";
        $sStr .= ",\nsearchLabel : '" . $this->translateOrPrint($this->aOptions['searchLabel']) . "'";
        $sStr .= ",\nsearchresetlabel : '" . $this->translateOrPrint($this->aOptions['searchresetlabel']) . "'";
        $sStr .= ",\neltLabel : '" . $this->translateOrPrint($this->aOptions['eltLabel']) . "'";
        $sStr .= ",\neltsParPageLabel : '" . $this->translateOrPrint($this->aOptions['eltsParPageLabel']) . "'";
        if (isset($this->aOptions['eltsPerPageChngCallback'])) {
            $sStr .= ",\neltsPerPageChngCallback : " . $this->aOptions['eltsPerPageChngCallback'];
        }
        if (isset($this->aOptions['aftertableload'])) {
            $sStr .= ",\naftertableload : " . $this->aOptions['aftertableload'];
        }
        if (isset($this->aOptions['csrfrefreshroute'])) {
            $sStr .= ",\ncsrfrefreshroute : '" . $this->aOptions['csrfrefreshroute'] . "'";
        }
        $sButtons = "";
        if (isset($this->aOptions['buttons']) && count($this->aOptions['buttons']) > 0) {

            foreach ($this->aOptions['buttons'] as $aValues) {
                if (strlen($sButtons) > 0)
                    $sButtons .= ",";
                $sButton = "";
                if (isset($aValues['id'])) {
                    if (strlen($sButton) > 0)
                        $sButton .= ",";
                    $sButton .= "id : '" . $aValues['id'] . "'";
                }
                if (isset($aValues['text'])) {
                    if (strlen($sButton) > 0)
                        $sButton .= ",";
                    $sButton .= "text : '" . addslashes($aValues['text']) . "'";
                }
                if (isset($aValues['title'])) {
                    if (strlen($sButton) > 0)
                        $sButton .= ",";
                    $sButton .= "title : '" . addslashes($aValues['title']) . "'";
                }
                if (isset($aValues['img'])) {
                    if (strlen($sButton) > 0)
                        $sButton .= ",";
                    $sButton .= "img : '" . $aValues['img'] . "'";
                }
                if (isset($aValues['em'])) {
                    if (strlen($sButton) > 0)
                        $sButton .= ",";
                    $sButton .= "em : '" . $aValues['em'] . "'";
                }
                if (isset($aValues['action'])) {
                    if (strlen($sButton) > 0)
                        $sButton .= ",";
                    $sButton .= "action : " . $aValues['action'];
                }
                $sButtons .= "{" . $sButton . "}\n";
            }
        }
        $sStr .= ",\nbuttons: [\n" . $sButtons . "],\n";
        $sStr .= "});\n";
        // $sStr .= $this->tablevar() . ".display();\n});\n</script>\n";
        $sStr .= "});\n</script>\n";
        return $sStr;
    }

    /**
     * returns a string or passes translation key to translation function
     *
     * @param string $key
     *            normal string or translation key surrounded by #
     * @return string text to display
     */
    private function translateOrPrint($key)
    {
        if (preg_match('/^\#(.+)\#$/', $key, $matches)) {
            return addslashes(__($matches[1]));
        }
        return $key;
    }
}
