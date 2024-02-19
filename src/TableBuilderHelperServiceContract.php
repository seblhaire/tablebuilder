<?php

namespace Seblhaire\TableBuilder;

use Illuminate\Http\Request;

interface TableBuilderHelperServiceContract {

    public function initTable($element, $url, $options);

    public function initColumn($type, $dataBindTo, $options);

    public function initDataBuilder(Request $request, $othervalidationrules = []);
}
