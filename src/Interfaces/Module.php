<?php declare(strict_types=1);

namespace Laraddon\Interfaces;

use Laraddon\Core;

abstract class Module {
    protected string $path;
    
    /**
     * @var array<string, mixed> $attributes
     */
    protected array $attributes = [
        'base' => '',
        'routes' => [],
        'api_routes' => false,
        'middleware_scopes' => [],
    ];
    protected string $class;
    
    /**
     * @param  string $class
     * @param  string $path
     * @return void
     */
    public function __construct(string $class, string $path) {
        $this->class = rtrim($class, '\\');
        $this->path = $path;
        $this->setAttributes();
    }
    
    /**
     * @return string
     */
    public function __toString(): string {
        return $this->attributes['base'];
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
     * @return string
     */
    public function getName(): string {
        return preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $this->attributes['base']);
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
        return $this->attributes['api_routes'] ?? false;
    }
    
    /**
     * setAttributes
     *
     * @return void
     */
    private function setAttributes(): void {
        $this->attributes['base'] = '/' . Core::camelToUnderscore(basename($this->class), '-');

        if (file_exists($this->path . '/init.php')) {
            $mergeAttribute = require_once $this->path . '/init.php';
            $this->attributes = array_merge($this->attributes, $mergeAttribute);
        }
    }
}