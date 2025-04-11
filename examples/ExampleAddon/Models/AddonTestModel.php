<?php

namespace Iqionly\Laraddon\ExampleAddon\Models;

class AddonTestModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'test_table';
    protected $fillable = ['id', 'column1', 'column2'];

    public function addon_test_relation()
    {
        return $this->belongsTo(AddonTestRelationModel::class, 'test_relation_table_id', 'id', 'test_relation_table');
    }
}