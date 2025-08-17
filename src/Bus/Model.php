<?php declare(strict_types=1);

namespace Laraddon\Bus;

use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\Types;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laraddon\Core;
use Laraddon\Interfaces\Databases\Field;
use Laraddon\Interfaces\Databases\Meta;
use Laraddon\Interfaces\Databases\Types\DefaultState;

abstract class Model extends EloquentModel {
    #[Meta]
    public string $_id;
    #[Meta]
    public string $_name = '';
    #[Meta]
    public string $_description = '';

    #[Field(primary: true, autoIncrement: true)]
    public IntegerType $id;
    public JsonType $name;
    #[Field(enum: [
        'show',
        'archived'
    ], default: 'show')]
    public DefaultState $state;

    public function __construct(array $attributes = [])
    {
        $this->_name = self::getNameModel($this::class);
        $this->_id = $this->_name;

        parent::__construct($attributes);
    }

    public static function getNameModel(string $name): string {
        return Core::camelToUnderscore(class_basename($name));
    }
}