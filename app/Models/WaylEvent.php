<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaylEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function data(): array
    {
        return json_decode($this->payload, true) ?: [];
    }
}
