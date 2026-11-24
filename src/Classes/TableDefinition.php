<?php

namespace Seblhaire\TableBuilder\Classes;

use Seblhaire\TableBuilder\Classes\Columns\AbstractTableColumn;
use Seblhaire\TableBuilder\Enums\TableParams;

use Illuminate\Http\Request;
/**
 * class that sets table contents and prints Javascript code needed by table object
 */
class TableDefinition {

    private ?string $sTableElement = null;
    private ?string $url = null;
    private ?array $aOptions = null;
    private ?array $aColumns = null;
    private array $initValues = [];

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
    public function __construct(string $tableElement, string $url, array $options = [], ?Request $request = null) {
        $this->sTableElement = $tableElement;
        $this->url = $url;
        $this->aOptions = array_merge(array_replace(config('tablebuilder.table'), $options), [
            'csrf' => csrf_token()
        ]);
        $this->aColumns = array();
        if (!is_null($request)){
            $valid = $request->validated();
            if (count($valid) > 0){
                if (
                    array_key_exists(config('tablebuilder.table.getattribute'), $valid) && 
                    array_key_exists($this->sTableElement, $valid[config('tablebuilder.table.getattribute')])
                ){
                    $this->initValues = $valid[config('tablebuilder.table.getattribute')][$this->sTableElement];
                }
            }
        }
    }

    /**
     * outputs table variable name
     *
     * @return string vaiiable name
     */
    public function tablevar() {
        return $this->sTableElement . "_table";
    }

    /**
     * adds a column object to table
     *
     * @param AbstractTableColumn $obj
     *            table column
     */
    public function addColumn($obj) {
        if (is_a($obj, AbstractTableColumn::class)) {
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
    public function outputReload(): string {
        return "$('#" . $this->sTableElement . "').data('tablebuilder').reload();";
    }

    public function buildAction(string $action): string {
        return "$('#" . $this->sTableElement . "').data('tablebuilder').applyaction(" . $action . ");";
    }

    /**
     * Outputs table definition
     *
     * @return string html + js code
     */
    public function printDiv():string {
        if (count($this->aColumns) == 0) {
            throw new \Exception('empty cols');
        }
        return "<div id=\"" . $this->sTableElement . "\"></div>\n";
    }

    public function printJsInit(): string {
        
        $sStr = "$('#" . $this->sTableElement . "').tablebuilder('" . $this->url . "', " . $this->printColumns() . ",";
        $sStr .= $this->printOptions() . "," .  $this->printInitValues().");";
        return $sStr;
    }

    private function printInitValues(): string {
        if (count($this->initValues) > 0) {
            return "{" . 
                TableParams::Itemsperpage->value . ': ' . ($this->initValues[TableParams::Itemsperpage->value] ?? $this->aOptions['itemsperpage']) . ',' . 
                TableParams::Start->value . ': ' . ($this->initValues[TableParams::Start->value] ??  0) . ',' .
                TableParams::Sortby->value . ': "' . ($this->initValues[TableParams::Sortby->value] ?? ''). '",' . 
                TableParams::Searchterm->value . ': "' . ($this->initValues[TableParams::Searchterm->value] ?? ''). '"}';
                
        } else{
            return "{" . 
                TableParams::Itemsperpage->value . ': ' . $this->aOptions['itemsperpage'] . ',' . 
                TableParams::Start->value . ': ' . 0 . ',' .
                TableParams::Sortby->value . ': "",' . 
                TableParams::Searchterm->value . ': ""}';
        }
    }

    private function printColumns(): string {
        $cols = '';
        foreach ($this->aColumns as $oColumn) {
            if ($cols != '')
                $cols .= ",\n";
            $cols .= "{type:'" . $oColumn->type() . "', data:" . $oColumn->dataBindTo() . ", options: " . $oColumn->printOptions() . "}";
        }
        return "[" . $cols . "]";
    }

    private function printOptions(): string {
        $sStr = "{searchable : " . ($this->aOptions['searchable'] == true ? 'true' : 'false');
        $sStr .= ', getattribute: "' . $this->aOptions['getattribute'];
        $sStr .= "\", updateurl : " . ($this->aOptions['updateurl'] == true ? 'true' : 'false');
        $sStr .= ", automode : " . ($this->aOptions['automode'] == true ? 'true' : 'false');
        $sStr .= ", modecard : " . ($this->aOptions['modecard'] == true ? 'true' : 'false');
        $sStr .= ", showcardmode : " . ($this->aOptions['showcardmode'] == true ? 'true' : 'false');
        $sStr .= ", addselectline : " . ($this->aOptions['addselectline'] == true ? 'true' : 'false');
        $sStr .= ", buildfilters : " . ($this->aOptions['buildfilters'] == true ? 'true' : 'false');
        $sStr .= ", breakpoint : " . $this->aOptions['breakpoint'];
        $sStr .= ", csrf : \"" . $this->aOptions['csrf'];
        $sStr .= "\", itemsperpage : " . $this->aOptions['itemsperpage'];
        $sStr .= ", pagechoices : [" . implode(",", $this->aOptions['pagechoices']) . "]";
        $sStr .= ", encoding : '" . $this->aOptions['encoding'] . "'";
        if (isset($this->aOptions['paramsFunction'])) {
            $sStr .= ", paramsFunction : " . $this->aOptions['paramsFunction'];
        }
        $p = '';
        if (isset($this->aOptions['additionalparams']) && count($this->aOptions['additionalparams']) > 0) {

            foreach ($this->aOptions['additionalparams'] as $sParam => $vValue) {
                if (strlen($p) > 0)
                    $p .= ",";
                $p .= "'" . $sParam . "' : '" . $vValue . "'";
            }
        }
        $sStr .= ", additionalParams : {" . $p . '}';
        $sStr .= ", tableClass : '" . $this->aOptions['tableClass'] . "'";
        $sStr .= ", headersclass : '" . $this->aOptions['headersclass'] . "'";
        $sStr .= ", searchdivclass : '" . $this->aOptions['searchdivclass'] . "'";
        $sStr .= ", searchinputgrpclass : '" . $this->aOptions['searchinputgrpclass'] . "'";
        $sStr .= ", searchinputclass: '" . $this->aOptions['searchinputclass'] . "'";
        $sStr .= ", searchresetbuttonclass : '" . $this->aOptions['searchresetbuttonclass'] . "'";
        $sStr .= ", searchresetbuttondivclass : '" . $this->aOptions['searchresetbuttondivclass'] . "'";
        $sStr .= ", togglebtnclass : '" . $this->aOptions['togglebtnclass'] . "'";
        $sStr .= ", modetablebtn : '" . $this->aOptions['modetablebtn'] . "'";
        $sStr .= ", modecardbtn : '" . $this->aOptions['modecardbtn'] . "'";
        $sStr .= ", filterbtn : '" . $this->aOptions['filterbtn'] . "'";
        if (isset($this->aOptions['filterdivid'])) {
            $sStr .= ", filterdivid : '" . $this->aOptions['filterdivid'] . "'";
        }
        $sStr .= ", eltspageclass : '" . $this->aOptions['eltspageclass'] . "'";
        $sStr .= ", paginationclass : '" . $this->aOptions['paginationclass'] . "'";
        $sStr .= ", pagecontclass : '" . $this->aOptions['pagecontclass'] . "'";
        $sStr .= ", bottomclass : '" . $this->aOptions['bottomclass'] . "'";
        $sStr .= ", footerclass : '" . $this->aOptions['footerclass'] . "'";
        $sStr .= ", cardgroupclass : '" . $this->aOptions['cardgroupclass'] . "'";
        $sStr .= ", cardcol : '" . $this->aOptions['cardcol'] . "'";
        $sStr .= ", cardclass : '" . $this->aOptions['cardclass'] . "'";
        $sStr .= ", cardselector : '" . $this->aOptions['cardselector'] . "'";
        $sStr .= ", cardcontentclass : '" . $this->aOptions['cardcontentclass'] . "'";
        $sStr .= ", cardfooter : '" . $this->aOptions['cardfooter'] . "'";
        $sStr .= ", rowcontextualtrigger : '" . $this->aOptions['rowcontextualtrigger'] . "'";
        $sStr .= ", ajaximgname : '" . $this->aOptions['ajaximgname'] . "'";
        $sStr .= ", uparrow : '" . $this->aOptions['uparrow'] . "'";
        $sStr .= ", downarrow : '" . $this->aOptions['downarrow'] . "'";
        $sStr .= ", leftarrow : '" . $this->aOptions['leftarrow'] . "'";
        $sStr .= ", dblleftarrow : '" . $this->aOptions['dblleftarrow'] . "'";
        $sStr .= ", rightarrow: '" . $this->aOptions['rightarrow'] . "'";
        $sStr .= ", dblrightarrow : '" . $this->aOptions['dblrightarrow'] . "'";
        $sStr .= ", buttondivarrow : '" . $this->aOptions['buttondivarrow'] . "'";
        $sStr .= ", ajaxerrormsg : '" . $this->translateOrPrint($this->aOptions['ajaxerrormsg']) . "'";
        $sStr .= ", nodatastr : '" . $this->translateOrPrint($this->aOptions['nodatastr']) . "'";
        $sStr .= ", chkheadlabel : '" . $this->translateOrPrint($this->aOptions['chkheadlabel']) . "'";
        $sStr .= ", selectelement : '" . $this->translateOrPrint($this->aOptions['selectelement']) . "'";
        $sStr .= ", searchLabel : '" . $this->translateOrPrint($this->aOptions['searchLabel']) . "'";
        $sStr .= ", searchresetlabel : '" . $this->translateOrPrint($this->aOptions['searchresetlabel']) . "'";
        $sStr .= ", eltLabel : '" . $this->translateOrPrint($this->aOptions['eltLabel']) . "'";
        $sStr .= ", eltsParPageLabel : '" . $this->translateOrPrint($this->aOptions['eltsParPageLabel']) . "'";
        $sStr .= ", tableModeLabel : '" . $this->translateOrPrint($this->aOptions['tableModeLabel']) . "'";
        $sStr .= ", cardModeLabel : '" . $this->translateOrPrint($this->aOptions['cardModeLabel']) . "'";
        $sStr .= ", togglefilters : '" . $this->translateOrPrint($this->aOptions['togglefilters']) . "'";
        if (isset($this->aOptions['eltsPerPageChngCallback'])) {
            $sStr .= ", eltsPerPageChngCallback : " . $this->aOptions['eltsPerPageChngCallback'];
        }
        if (isset($this->aOptions['aftertableload'])) {
            $sStr .= ", aftertableload : " . $this->aOptions['aftertableload'];
        }
        if (isset($this->aOptions['customprintdatacard'])) {
            $sStr .= ", customprintdatacard : " . $this->aOptions['customprintdatacard'];
        }
        return $sStr . '}';
    }

    /**
     * returns a string or passes translation key to translation function
     *
     * @param string $key
     *            normal string or translation key surrounded by #
     * @return string text to display
     */
    private function translateOrPrint($key) {
        if (preg_match('/^\#(.+)\#$/', $key, $matches)) {
            return addslashes(__($matches[1]));
        }
        return $key;
    }
}
