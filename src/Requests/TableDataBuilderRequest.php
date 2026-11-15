<?php

namespace Seblhaire\TableBuilder\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Seblhaire\TableBuilder\Enums\TableParams;

class TableDataBuilderRequest extends FormRequest {
	/**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            TableParams::Itemsperpage->value => 'required|numeric',
            TableParams::Sortby->value => 'string|nullable',
            TableParams::Start->value => 'required|numeric',
            TableParams::Searchterm->value => 'string|nullable'
        ];
    }
}