<?php declare(strict_types=1);

namespace Laraddon\Attributes\Routes;

/**
 * 
 * @var array<int, string> $middleware_groups
 * @var bool $generate_api
 * @var array<int, string> $excluded_routes
 */
trait HasRoutes
{
     /**
     * @var array<int, string> $middleware_groups
     */
    protected array $middleware_groups;
    /**
     * @var bool $generate_api
     */
    protected bool $generate_api;
    /**
     * @var array<int, string> $excluded_routes
     */
    protected array $excluded_routes;
}