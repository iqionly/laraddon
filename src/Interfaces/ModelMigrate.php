<?php declare(strict_types=1);

namespace Iqionly\Laraddon\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;
use ReflectionProperty;

abstract class ModelMigrate extends Model {

    /**
     * @param  array<string> $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get the column name for the table model.
     *
     * @return array<mixed>
     */
    public function columns(): array {
        return [];
    }
}