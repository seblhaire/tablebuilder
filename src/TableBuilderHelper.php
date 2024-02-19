<?php

namespace Seblhaire\TableBuilder;

use Illuminate\Support\Facades\Facade;

class TableBuilderHelper extends Facade {

    /**
     * Builds a facade
     *
     * @return [type] [description]
     */
    protected static function getFacadeAccessor() {
        return 'TableBuilderHelperService';
    }
}
