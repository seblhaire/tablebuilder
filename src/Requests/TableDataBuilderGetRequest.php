<?php

namespace Seblhaire\TableBuilder\Requests;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;
use Seblhaire\TableBuilder\Enums\TableParams;

// http://demoseb.test/tablebuilder/dynamic?tables={"tabtest"%3A{"itemsperpage"%3A20%2C"start"%3A0%2C"sortBy"%3A"birthday%3Aasc"%2C"searchTerm"%3A""}}
// http://demoseb.test/tablebuilder/dynamic?tables={"tabtest"%3A{"itemsperpage"%3A20%2C"start"%3A0%2C"sortBy"%3A"birthday%3Aasc"%2C"searchTerm"%3A""%2C"startdate"%3A"2026-11-11"%2C"enddate"%3A"2026-11-17"}}
// http://demoseb.test/tablebuilder/dynamic?tables={"tabtest"%3A{"itemsperpage"%3A20%2C"start"%3A0%2C"sortBy"%3A"birthday%3Aasc"%2C"searchTerm"%3A""%2C"startdate"%3A"2026-11-11"%2C"enddate"%3A"2026-11-17"%2C"toto"%3A"tutu"}}
class TableDataBuilderGetRequest extends FormRequest {
	
	protected $mandatoryParams =  [
	    TableParams::Itemsperpage->value => 'required|numeric',
	    TableParams::Sortby->value => 'string|nullable',
	    TableParams::Start->value => 'required|numeric',
	    TableParams::Searchterm->value => 'string|nullable'
    ];

    public $tableNames = [];

    public $additionalParams = [];

    protected function prepareForValidation(): void
    {
        if ($this->filled(config('tablebuilder.table.getattribute'))) {
        	$data = json_decode($this->input(config('tablebuilder.table.getattribute')), true);
            if (count($data) > 0){
                $merge = [];
                foreach ($data as $table => $params){
                    foreach ($params as $key => $value){
                        $merge[config('tablebuilder.table.getattribute'). ".{$table}.{$key}"] = $value;
                    }
                }
            }
            $this->merge($merge);
        }
    }

    public function rules() : array{
    	$rules = [];
    	$rules[config('tablebuilder.table.getattribute')] = 'sometimes';
    	$subfield = config('tablebuilder.table.getattribute') . '.*';
    	$rules[$subfield] = Rule::in($this->tableNames);
    	foreach ($this->mandatoryParams as $key => $values){
    		$rules[$subfield . '.' . $key] = $values;
    	}
    	if (count($this->additionalParams)){
    		foreach ($this->additionalParams as $key => $values){
                $rules[$subfield . '.' . $key] = $values;
    		}
    	}
    	return $rules;
    }

}
