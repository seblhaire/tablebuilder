<?php
namespace Seblhaire\TableBuilder;

use Illuminate\Http\Request;

class TableBuilderHelperService implements TableBuilderHelperServiceContract
{

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
    public function initTable($element, $url, $options = [])
    {
        return new TableDefinition($element, $url, $options);
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
    public function initColumn($type, $dataBindTo, $options = [])
    {
        if (! in_array($type, [
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
        $classname = "\Seblhaire\TableBuilder\\" . ucfirst($type) . 'Cell';
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
    public function initDataBuilder(Request $request, $othervalidationrules = [])
    {
        return new TableDataBuilder($request, $othervalidationrules);
    }
}
