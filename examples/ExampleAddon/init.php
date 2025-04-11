<?php

use Iqionly\Laraddon\ExampleAddon\Controllers\AddonTestController;
use Iqionly\Laraddon\ExampleAddon\Models\AddonTestModel;
use Iqionly\Laraddon\ExampleAddon\Models\AddonTestRelationModel;

return [
    'controllers' => [
        AddonTestController::class
    ],
    'models' => [
        AddonTestModel::class,
        AddonTestRelationModel::class,
    ],
    'views' => [
        'test.blade.php'
    ]
];