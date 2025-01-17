<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class FileUploadsModel extends Model
{
    protected $table = 'file_uploads';
    protected $fillable = [
        'file_name',
        'file_path',
        'aes_key',
        'file_size',
        'mime_type',
        'uploaded_by',
        'enc_type'
    ];
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
