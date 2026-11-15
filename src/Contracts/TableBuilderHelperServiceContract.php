<?php

namespace Seblhaire\TableBuilder\Contracts;

use Illuminate\Http\Request;

interface TableBuilderHelperServiceContract {

    public function initTable(string $element, string $url, array $options = [], ?Request $request = null);

    public function initColumn(string $type, string $dataBindTo, array $options = []);

    public function initDataBuilder(Request $request, array $othervalidationrules = []);
}
