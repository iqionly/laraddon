<?php declare(strict_types=1);

namespace Laraddon\Annotated;

use Attribute;

#[Attribute]
class Method {
    const GET         = 'get';
    const POST        = 'post';
    const PUT         = 'put';
    const PATCH       = 'patch';
    const DELETE      = 'delete';
    const OPTION      = 'options';
    const ANY         = 'any';
}