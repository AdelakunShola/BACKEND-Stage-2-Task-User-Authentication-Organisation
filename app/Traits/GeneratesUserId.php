<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesUserId
{
    protected static function bootGeneratesUserId()
    {
        static::creating(function ($model) {
            $model->userId = (string) Str::uuid(); // You can use Str::random() if you prefer random strings
        });
    }
}