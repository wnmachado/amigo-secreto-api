<?php

namespace App\Modules\Participants\Models;

use App\Modules\Events\Models\Event;
use App\Modules\Draw\Models\DrawResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'whatsapp_number',
        'code',
        'gift_suggestion',
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
    public function drawResultsAsGiver(): HasOne
    {
        return $this->hasOne(DrawResult::class, 'giver_participant_id');
    }

    /**
     * Get the draw results where this participant is the receiver.
     */
    public function drawResultsAsReceiver(): HasOne
    {
        return $this->hasOne(DrawResult::class, 'receiver_participant_id');
    }
}
