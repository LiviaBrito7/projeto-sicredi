<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $fillable = [
        'original_name',
        'path',
        'size',
        'status',
        'processed_at'
    ];
}
