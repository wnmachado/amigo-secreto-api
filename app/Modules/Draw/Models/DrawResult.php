<?php

namespace App\Modules\Draw\Models;

use App\Modules\Events\Models\Event;
use App\Modules\Participants\Models\Participant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawResult extends Model
{
    protected $fillable = [
        'event_id',
        'giver_participant_id',
        'receiver_participant_id',
    ];

    /**
     * Get the event that owns the draw result.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the giver participant.
     */
    public function giver(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'giver_participant_id');
    }

    /**
     * Get the receiver participant.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'receiver_participant_id');
    }
}
