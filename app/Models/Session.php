<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_activity' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the last activity as a Carbon instance.
     */
    public function getLastActivityAttribute($value)
    {
        return $this->attributes['last_activity'] ? now()->createFromTimestamp($this->attributes['last_activity']) : null;
    }

    /**
     * Scope to get sessions active within a given timeframe.
     */
    public function scopeActiveWithin($query, $hours = 2)
    {
        $cutoff = now()->subHours($hours)->timestamp;

        return $query->where('last_activity', '>=', $cutoff);
    }
}
