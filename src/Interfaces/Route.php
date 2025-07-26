<?php declare(strict_types=1);

namespace Laraddon\Interfaces;

interface Route {
    public function __construct(string $get = '/', string $post = '/', string $put = '/', string $delete = '/');
}