<?php

namespace App\Modules\Participants\Models;

use App\Modules\Events\Models\Event;
use App\Modules\Draw\Models\DrawResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'whatsapp_number',
        'is_confirmed',
    ];

    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
        ];
    }

    /**
     * Get the event that owns the participant.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the draw results where this participant is the giver.
     */
    public function drawResultsAsGiver(): HasMany
    {
        return $this->hasMany(DrawResult::class, 'giver_participant_id');
    }

    /**
     * Get the draw results where this participant is the receiver.
     */
    public function drawResultsAsReceiver(): HasMany
    {
        return $this->hasMany(DrawResult::class, 'receiver_participant_id');
    }
}
