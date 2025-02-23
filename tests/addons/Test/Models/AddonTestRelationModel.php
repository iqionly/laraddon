<?php

namespace Iqionly\Laraddon\Tests\Addons\Test\Models;

class AddonTestRelationModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'test_relation_table';
    protected $fillable = ['id', 'test_relation_table_id', 'column1', 'column2'];
}