<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    protected function casts(): array
    {
        return [
            'task_date' => 'date',
            'status' => 'integer',
            'task_category_id' => 'integer',
        ];
    }
}
