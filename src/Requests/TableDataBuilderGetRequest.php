<?php

namespace Seblhaire\TableBuilder\Requests;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;
use Seblhaire\TableBuilder\Enums\TableParams;

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

    		}
    	}
    	return $rules;
    }

}
