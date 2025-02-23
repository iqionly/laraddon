<?php

use Iqionly\Laraddon\Tests\Addons\Test\Controllers\AddonTestController;
use Iqionly\Laraddon\Tests\Addons\Test\Models\AddonTestModel;
use Iqionly\Laraddon\Tests\Addons\Test\Models\AddonTestRelationModel;

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