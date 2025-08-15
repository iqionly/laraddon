<?php declare(strict_types=1);

namespace Laraddon\Bus;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Foundation\Application;
use Laraddon\Core;

enum DefaultState {
    case Show;
    case Hidden;
}

abstract class Model extends EloquentModel {
    public string $_id;
    
    public string $_name = '';
    public string $_description = '';

    public function __construct(array $attributes = [])
    {
        $this->_name = Core::camelToUnderscore(class_basename($this));

        parent::__construct($attributes);
    }

    protected static function boot()
    {
        $app = Application::getInstance();
        dd($app->get(Core::class));
    }
    
    public string $name;
    public DefaultState $state;
}