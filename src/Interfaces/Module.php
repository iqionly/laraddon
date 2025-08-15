<?php declare(strict_types=1);

namespace Laraddon\Interfaces;

use Laraddon\Core;

abstract class Module {
    protected string $path;

    protected string $base;

    protected string $name;

    protected bool $api_routes = false;
    
    /**
     * @var array<string, mixed> $attributes
     */
    protected array $attributes = [
        'routes' => [],
        'middleware_scopes' => [],
    ];
    protected string $class;
    
    /**
     * @param  string $class
     * @param  string $path
     * @return void
     */
    public function __construct(string $class, string $path) {
        $this->name = Core::camelToUnderscore(class_basename($class));
        $this->class = rtrim($class, '\\');
        $this->path = $path;
        $this->setAttributes();
    }
    
    /**
     * @return string
     */
    public function __toString(): string {
        return $this->base;
    }
    
    /**
     * getClass
     *
     * @return string
     */
    public function getClass(): string {
        return $this->class;
    }
    
    /**
     * getName
     * 
     * @throws \Exception The module not have name
     *
     * @return string
     */
    public function getName(): string {
        if($name = preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $this->base)) {
            return $name;
        }
        throw new \Exception("No name for module", 10100);
    }
    
    /**
     * getPath
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * getAttributes
     *
     * @return array<string, mixed> $attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * getApiRoutesAttribute
     *
     * @return bool
     */
    public function getApiRoutesAttribute(): bool
    {
        return $this->api_routes ?? false;
    }
    
    /**
     * setAttributes
     *
     * @return void
     */
    private function setAttributes(): void {
        $this->base = '/' . str_replace('\\', '/', class_basename($this->class));
        if (file_exists($this->path . '/init.php')) {
            $mergeAttribute = require_once $this->path . '/init.php';
            if(!is_array($mergeAttribute)) {
                throw new \Exception("Module init.php file must return an array", 10101);
            }
            foreach(array_diff_assoc($mergeAttribute, $this->attributes) as $ka => $va) {
                $this->{$ka} = $va;
            }
            $this->attributes = array_diff_assoc($this->attributes, $mergeAttribute);
        }
    }
}