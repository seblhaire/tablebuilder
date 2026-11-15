<?php

namespace Seblhaire\TableBuilder\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Support\Facades\Validator;

class TableGetParamsRule implements ValidationRule
{
    /*
    * @params array $tableids = []
    */
    public function __construct(protected array $tableids = [])
    {
        // code...
    }
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($attribute == config('tablebuilder.table.getattribute')){
            if (json_validate($value)){
                $jsonarr = json_decode($value, true);
                dd($jsonarr);
                foreach($jsonarr as $table => $params){
                    if (count($this->tableids) > 0){
                        if (!isset($this->tableids[$table])){
                            $fail('Unknown table id');
                        }
                        $validator = Validator::make($params, $this->tableids[$table]);
                        if ($validator->fails()) {
                            $fail('additional params error');
                        }
                    }
                    if (!isset($params[TableParams::Itemsperpage->value]) || !is_numeric($params[TableParams::Itemsperpage->value])){
                        $fail('wrong param ' . TableParams::Itemsperpage->value);
                    }
                    if (!isset($params[TableParams::Sortby->value]) || !is_string($params[TableParams::Sortby->value])){
                        $fail('wrong param ' . TableParams::Sortby->value);
                    }
                    if (!isset($params[TableParams::Start->value]) || !is_numeric($params[TableParams::Start->value])){
                        $fail('wrong param ' . TableParams::Start->value);
                    }
                    if (!isset($params[TableParams::Searchterm->value]) || !is_string($params[TableParams::Searchterm->value])){
                        $fail('wrong param ' . TableParams::Searchterm->value);
                    }
                }
            } else {
                $fail('invalid json');
            }
        } else {
            $fail('Rule only for tablebuilder, use config tablebuilder.table.getattribute.');
        }
    }
}
