<?php

namespace Seblhaire\TableBuilder\Services;

use Seblhaire\TableBuilder\Contracts\TableBuilderHelperServiceContract;
use Seblhaire\TableBuilder\Classes\TableDefinition;
use Seblhaire\TableBuilder\Classes\TableDataBuilder;

use Illuminate\Http\Request;

class TableBuilderHelperService implements TableBuilderHelperServiceContract {

    /**
     * inits a table definition
     *
     * @param string $element
     *            table id
     * @param string $url
     *            route to get table data
     * @param array $options
     *            table options
     * @return Seblhaire\TableBuilder\TableDefinition definition object
     */
    public function initTable(string $element, string $url, array $options = [], ?Request $request = null) {
        return new TableDefinition($element, $url, $options, $request);
    }

    /**
     * inits a new column
     *
     * @param string $type
     *            column type: 'action', 'checkbox', 'data', 'date', 'image', 'link', 'mail', 'numeric', 'status'
     * @param string $dataBindTo
     *            field in data that contains column content
     * @param array $options
     *            options list
     * @return Seblhaire\TableBuilder\AbstractTableColumn a column object to be added to table
     */
    public function initColumn(string $type, string $dataBindTo, array $options = []) {
        if (!in_array($type, [
                    'action',
                    'checkbox',
                    'data',
                    'date',
                    'image',
                    'link',
                    'mail',
                    'numeric',
                    'status'
                ]))
            throw new \Exception('wrong column type ' . $type);
        $classname = "\Seblhaire\TableBuilder\Classes\Columns\\" . ucfirst($type) . 'Cell';
        return new $classname($dataBindTo, $options);
    }

    /**
     * inits a data builder that can be used by controller to send data to table
     *
     * @param Request $request
     *            parameters sent by table to controller
     * @param array $othervalidationrules
     *            validation rules for additional custom parameters sent by table
     * @return Seblhaire\TableBuilder\TableDataBuilder object that buidds data
     */
    public function initDataBuilder(Request $request, array $othervalidationrules = []) {
        return new TableDataBuilder($request, $othervalidationrules);
    }
}
