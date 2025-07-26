<?php declare(strict_types=1);

namespace Laraddon\Registerer;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ErrorException;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Laraddon\Core;
use Laraddon\Errors\InvalidModules;
use Laraddon\Interfaces\Initiable;
use Laraddon\Interfaces\Module;

class ControllerRegisterer extends Registerer implements Initiable
{
    public const string CONTROLLER_PATH_MODULE = 'Controllers';

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
        if(is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $file = str_replace('.php', '', $file);
                $modulePath = $module->getClass() . '\\' . self::CONTROLLER_PATH_MODULE . '\\' . $file;
                if(class_exists($modulePath)) {
                    $this->extractRoute($module->getName(), new \ReflectionClass($modulePath), $module);
                }
            }
        } else {
            throw new InvalidModules("Views folder not found in $module", 13001);
        }
    }
    
    /**
     * Filter route without excluded routes
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
            if(count($attributes) == 0) {
                $attribute = [ 'get' => $name_method ];
            } else {
                $attribute = $attributes[0]->getArguments();
            }
            
            $method = array_key_first($attribute);
            if(!is_string($method)) {
                throw new ErrorException("Method is not string", 13002);
            }
            $uri = str_replace('_', '-', $attribute[$method]);
            foreach ($parameters as $param) {
                $type = $param->getType();

                if($type instanceof ReflectionNamedType && $type->getName() != Request::class)
                    $uri .= "/{" . strtolower($param->getName()) . "}";
                elseif($param instanceof ReflectionParameter)
                    $uri .= "/{" . strtolower($param->getName()) . "}";
            }
            foreach ($this->middleware_groups as $groupkey => $group) {
                // Detect if projects using default middleware api, we will add prefix api
                $result_method = strtoupper($method);
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
                $route_name .= Core::camelToUnderscore($name, '-') . '.' . Core::camelToUnderscore($uri, '-');
                $route_name = str_replace('/', '.', Core::removeParenthesis($route_name));
                $route->name($route_name);
            }
        }
    }
}