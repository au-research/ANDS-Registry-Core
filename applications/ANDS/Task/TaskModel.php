<?php

namespace ANDS\Task;

use Illuminate\Database\Eloquent\Model;

class TaskModel extends Model
{
    protected $table = "tasks";

    public $timestamps = false;

    protected $casts = [
        'data' => 'array',
        'message' => 'array'
    ];

    protected $fillable = ['name', 'status', 'data', 'params', 'type', 'message', 'date_added', 'next_run', 'last_run'];
}