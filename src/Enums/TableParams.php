<?php
namespace Seblhaire\TableBuilder\Enums;


enum TableParams: string { 
	case Itemsperpage = 'itemsperpage';
    case Sortby = 'sortBy';
    case Start = 'start';
    case Searchterm = 'searchTerm';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
        
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function asArray(): array
    {
        if (empty(self::values())) {
            return self::names();
        }
        
        if (empty(self::names())) {
            return self::values();
        }
        
        return array_column(self::cases(), 'value', 'name');
    }
}