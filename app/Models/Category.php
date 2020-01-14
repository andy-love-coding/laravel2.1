<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false; // 数据表为生成时间戳，此处告知 Laravel 不需要维护 created_at 和 updated_at

    protected $fillable = [
        'name', 'description',
    ];
}
