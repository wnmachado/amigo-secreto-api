<?php

namespace App\Modules\Events\Models;

use App\Modules\Users\Models\Users;
use App\Modules\Participants\Models\Participant;
use App\Modules\Draw\Models\DrawResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'event_date',
        'min_value',
        'max_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->uuid)) {
                $event->uuid = \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * Get the participants for the event.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get the draw results for the event.
     */
    public function drawResults(): HasMany
    {
        return $this->hasMany(DrawResult::class);
    }

    /**
     * Alias for draw results to expose "pairs" relationship.
     */
    public function pairs(): HasMany
    {
        return $this->drawResults()->with(['giver', 'receiver']);
    }

    /**
     * Get the confirmed participants count.
     */
    public function getConfirmedParticipantsCountAttribute(): int
    {
        return $this->participants()->where('is_confirmed', true)->count();
    }

    /**
     * Get the participants count.
     */
    public function getParticipantsCountAttribute(): int
    {
        return $this->participants()->count();
    }
}
