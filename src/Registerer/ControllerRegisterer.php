<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use Illuminate\Routing\Router;
use Laraddon\Annotated\Route;
use Laraddon\Core;
use Laraddon\Interfaces\Initiable;
use Laraddon\Interfaces\Module;

class ControllerRegisterer extends Registerer implements Initiable
{
    public const CONTROLLER_PATH_MODULE = 'Controllers';

    public function init(): self {
        return $this;
    }

    public function registerRoute(Module &$module): void {
        // Check if setting global generate api is true, and check scope module use api or not
        if($this->generate_api && !$module->getApiRoutesAttribute()) {
            $this->middleware_groups = array_filter($this->middleware_groups, function ($val) {
                return $val != 'api';
            });
        }

        $path = $module->getPath() . '/' . self::CONTROLLER_PATH_MODULE;
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $file = str_replace('.php', '', $file);
            $modulePath = $module->getClass() . '\\' . self::CONTROLLER_PATH_MODULE . '\\' . $file;
            if(class_exists($modulePath)) {
                $this->extractRoute($module->getName(), new \ReflectionClass($modulePath), $module);
            }
        }
    }
    
    /**
     * Filter route based on excluded routes
     *
     * @param  \ReflectionClass<object> $reflect
     * @return \ReflectionMethod[]
     */
    private function filterRoutes(\ReflectionClass $reflect): array {
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_filter($methods, function(ReflectionMethod $method) {
            return !in_array($method->getName(), $this->excluded_routes);
        });
    }

    /**
     * @param \ReflectionNamedType $type
     * @param string $name
     * 
     * @return string
     */
    private function extractType(\ReflectionNamedType $type, string $name) {
        if(!$this->app->bound($type->getName())) {
            $optional = $type->allowsNull() ? '?' : '';
            return "/{" . strtolower($name) . $optional . "}";
        }
        return '';
    }
    
    /**
     * Extract route from controller ReflectionClass
     *
     * @param  string $name
     * @param  ReflectionClass<object> $reflect
     * @param  Module $module
     * @return void
     */
    private function extractRoute(string $name, ReflectionClass $reflect, Module &$module): void {
        $methods = $this->filterRoutes($reflect);
        foreach ($methods as $method) {
            $parameters = $method->getParameters();
            $name_method = $method->getName();
            $attributes = $method->getAttributes();
            $attribute = [ 'get' => $name_method ];
            if($attributes){
                foreach($attributes as $attr) {
                    if($attr->getName() != Route::class) {
                        continue;
                    }

                    /** @var array<int,string> $values */
                    $values = array_fill(0, count($attr->getArguments()), $name_method);
                    
                    /** @var array<int,string> $keys */
                    $keys = $attr->getArguments();

                    /** @var array<int,string> $attribute */
                    $attribute = array_combine($keys, $values);
                }
            }
            
            $result_method = [];
            if(array_key_exists('any', $attribute)) {
                // Lakukan sekali foreach
                $result_method = Router::$verbs;
            } else {
                foreach ($attribute as $kmethod => $uri) {
                    if(is_numeric($kmethod)) {
                        throw new \Exception("Method is numeric, need to be string: get, post, put, etc.", 15200);
                    }
                    $result_method[] = strtoupper($kmethod);
                }
                $result_method[] = 'HEAD';
            }

            $uri = str_replace('_', '-', $name_method);
            foreach ($parameters as $param) {
                $paramType = $param->getType();
                if(!$paramType || !is_a($paramType, \ReflectionNamedType::class, true)) {
                    throw new \Exception("Param need to be ReflectionNamedType.", 15201);
                }
                $uri .= $this->extractType($paramType, $param->getName());
            }

            foreach ($this->middleware_groups as $groupkey => $group) {
                // Detect if projects using default middleware api, we will add prefix api
                if($method == 'any') {
                    $result_method = Router::$verbs;
                }
                $result = [
                    $result_method,
                    Core::camelToUnderscore($name, '-') . '/' . $uri,
                    [$reflect->getName(), $name_method]
                ];
                if($group == 'api') {
                    // Add prefix in first, because if we use method prefix(), it will be added in uri also, and we don't want
                    $result[2]['prefix'] = $group;
                }
                $route = $this->router->addRoute(...$result);
                $route->middleware($group);
                $route_name = $group == 'api' ? 'api.' : '';
                $route_name .= Core::camelToUnderscore($name, '-') . '.' . str_replace('_', '-', $name_method);
                $route_name = str_replace('/', '.', Core::removeParenthesis($route_name));
                $route->name($route_name);
            }
        }
    }
}