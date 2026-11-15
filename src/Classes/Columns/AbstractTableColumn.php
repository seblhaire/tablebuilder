<?php

namespace Seblhaire\TableBuilder\Classes\Columns;

/**
 * Generic table column object that contains common methods
 *
 * @author seb
 */
abstract class AbstractTableColumn {

    protected string $name = '';
    protected string $type = '';
    protected ?string $dataBindTo = null;
    protected ?array $aOptions = null;
    protected ?array $aCheckOptions = null;

    /**
     * javascript object name for column
     *
     * @return string
     */
    public function name() {
        return $this->name;
    }

    /**
     * Column type getter
     *
     * @return string
     */
    public function type() {
        return $this->type;
    }

    /**
     * get field name in data
     *
     * @return string
     */
    public function dataBindTo() {
        if (is_null($this->dataBindTo)) {
            return 'null';
        } else {
            return "'" . $this->dataBindTo . "'";
        }
    }

    /**
     * prints options common to every columns
     *
     * @return string
     */
    protected function _options() {
        $sStr = "";
        if (isset($this->aOptions['title'])) {
            $sStr .= "title : '" . $this->aOptions['title'] . "'";
        }
        if (isset($this->aOptions['completetitle'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "completetitle : '" . $this->aOptions['completetitle'] . "'";
        }
        if (isset($this->aOptions['width'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "width : '" . $this->aOptions['width'] . "'";
        }
        if (isset($this->aOptions['classes'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "classes : '" . $this->aOptions['classes'] . "'";
        }
        if (isset($this->aOptions['cardcontentclass'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "cardcontentclass : '" . $this->aOptions['cardcontentclass'] . "'";
        }
        if (isset($this->aOptions['sortable'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "sortable : " . ($this->aOptions['sortable'] == true ? 'true' : 'false');
        }
        if (isset($this->aOptions['defaultOrder'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "defaultOrder : '" . $this->aOptions['defaultOrder'] . "'";
        }
        if (isset($this->aOptions['customAsc'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "customAsc : '" . $this->aOptions['customAsc'] . "'";
        }
        if (isset($this->aOptions['customDesc'])) {
            if (strlen($sStr) > 0)
                $sStr .= ',';
            $sStr .= "customDesc : '" . $this->aOptions['customDesc'] . "'";
        }
        return $sStr;
    }

    /**
     * prints column options
     *
     * @return string
     */
    abstract public function printOptions();

    /**
     * either gets translation if appropriate translation key surronded by # sign or simply returns string
     *
     * @param string $key
     *            translation key surronded by # sign or simple string
     * @return string translated text or simple string
     */
    protected function translateOrPrint($key) {
        if (preg_match('/^\#(.+)\#$/', $key, $matches)) {
            return addslashes(__($matches[1]));
        }
        return $key;
    }
}
