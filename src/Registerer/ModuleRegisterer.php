<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint;
use Laraddon\Bus\Model;
use Laraddon\Bus\Module;
use Laraddon\Core;
use Laraddon\Interfaces\Initiable;

class ModuleRegisterer implements Initiable {
    const MODEL_PATH_MODULE = 'Models';

    protected Application $app;

    protected array $configDatabase = [];

    protected array $configConnection = [];

    protected Core $core;

    public function __construct(Application $app, Core $core)
    {
        $this->app = $app;
        $this->configDatabase = $app->get('config')->get('database', []);
        $this->configConnection = $this->configDatabase['connections'][$this->configDatabase['default']];
        $this->core = $core;
    }

    
    /**
     * init
     *
     * @return self
     */
    public function init(): self {
        $this->mappModels($this->core->getListModules());
        return $this;
    }

    /**
     * @param array<int,Module> $modules
     * 
     * @return void
     */
    private function mappModels(array $modules): void {
        /**
         * TODO: This method need to be simpled
         * because the function is to complex, i just dump it in one function from now
         */
        foreach ($modules as $module) {
            foreach ($module->getModels() as $model) {
                $file = str_replace('.php', '', basename($model));
                $modelPath = $module->getClass() . '\\' . self::MODEL_PATH_MODULE . '\\' . $file;
                if (!class_exists($modelPath)) { // TODO: Need error handling exceptions, for now just skip
                    continue;
                }
                $abstraction = new \ReflectionClass($modelPath);
                $_name = $modelPath::getNameModel($abstraction->getName());
                $_id = $_name;
                $properties = array_filter($abstraction->getProperties(\ReflectionProperty::IS_PUBLIC), function(\ReflectionProperty $v, $k) {
                    return count($v->getAttributes()) > 0;
                }, ARRAY_FILTER_USE_BOTH);
                $meta = [];
                $columns = array_filter($properties, function(\ReflectionProperty $v, $k) use (&$meta) {
                    if($v->getAttributes(\Laraddon\Interfaces\Databases\Meta::class)) {
                        $meta[$v->getName()] = $v;
                        return false;
                    }
                    return true;
                }, ARRAY_FILTER_USE_BOTH);
                usort($columns, function(\ReflectionProperty $a, \ReflectionProperty $b) {
                    return $a->class == Model::class ? -1 : 1; // Ensure Model properties are first
                });

                $capsule = new Capsule;

                $capsule->addConnection($this->configConnection);

                // Set the event dispatcher used by Eloquent models... (optional)
                $capsule->setEventDispatcher(new Dispatcher(new Container));

                // Make this Capsule instance available globally via static methods... (optional)
                $capsule->setAsGlobal();

                $capsule->getConnection()?->beginTransaction();

                // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
                $capsule->bootEloquent();

                // $capsule->schema()->create($_name, function (Blueprint $table) use ($columns) {
                //     foreach ($columns as $column) {
                //         $attribute = $column->getAttributes(\Laraddon\Interfaces\Databases\Field::class)[0] ?? 'string';
                //         // dump($attribute->getArguments());
                //         $type = $column->getType();
                //         $name = $type->getName();
                //         // dump(new $name());
                //         // if ($type) {
                //         //     $table->{$type->getName()}($column->getName());
                //         // } else {
                //         //     $table->string($column->getName());
                //         // }
                //     }
                //     // dd(true);
                //     $table->primary('id');
                // });

                // dd($capsule);
            }
        }
    }
}