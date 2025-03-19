<?php

namespace Iqionly\Laraddon\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;
use ReflectionProperty;

abstract class ModelMigrate extends Model {
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function columns() {
        return [];
    }
}