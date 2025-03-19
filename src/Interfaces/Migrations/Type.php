<?php

namespace Iqionly\Laraddon\Interfaces\Migrations;

use Illuminate\Database\Schema\Blueprint;

abstract class Type {
    protected string $query_type = '';

    public static function string($column, $length = 255, $default = null) {
        dd(app(static::class));
        // $stringType = new StringType($column, $length);
        // $stringType->query_type = $column . ' VARCHAR(' . $length . ')';
        // return $stringType;
    }

    public static function integer($column) {
        // $integerType = new IntegerType($column);
        // $integerType->query_type = $column . ' INT';
        // return $integerType;
    }
}