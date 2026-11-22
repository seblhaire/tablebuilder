<?php

namespace Seblhaire\TableBuilder\Requests;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

use Seblhaire\TableBuilder\Enums\TableParams;

class TableDataBuilderGetRequest extends FormRequest {
	
	protected $mandatoryParams =  [
	    TableParams::Itemsperpage->value => 'numeric',
	    TableParams::Sortby->value => 'string|nullable',
	    TableParams::Start->value => 'numeric',
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
    	$rules[config('tablebuilder.table.getattribute')] = ['sometimes', 'array', 'required_array_keys:' . implode(',',$this->tableNames)];
    	$subfield = config('tablebuilder.table.getattribute') . '.*';
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

