<?php

namespace Iqionly\Laraddon\Addons\Base\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Iqionly\Laraddon\Interfaces\Migrations\IntegerType;
use Iqionly\Laraddon\Interfaces\Migrations\StringType;
use Iqionly\Laraddon\Interfaces\ModelMigrate;

class TestModel extends ModelMigrate
{
    public function columns()
    {
        Schema::create('test', function (Blueprint $table) {
            $table->id();
            $table->string('test');
            $table->integer('test2');
            $table->timestamps();
        });
        $this->test = StringType::string('test');
        $this->test2 = IntegerType::integer('test2');
        return $this;
    }
}