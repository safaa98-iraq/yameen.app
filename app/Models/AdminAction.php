<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Audit log of admin changes (subscriptions, roles, settings, orders). */
class AdminAction extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record(string $action, ?string $subject = null, array $details = []): void
    {
        static::create(['admin_id' => auth()->id(), 'action' => $action, 'subject' => $subject, 'details' => $details ?: null]);
    }
}
