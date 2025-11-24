<?php

namespace App\Modules\Draw\Services;

use App\Modules\Draw\Repositories\DrawResultRepository;
use App\Modules\Participants\Repositories\ParticipantRepository;
use App\Modules\Events\Repositories\EventRepository;
use App\Modules\Events\Models\Event;
use App\Plugins\WhatsApp;
use Illuminate\Support\Collection;

class DrawService
{
    public function __construct(
        private DrawResultRepository $drawResultRepository,
        private ParticipantRepository $participantRepository,
        private EventRepository $eventRepository
    ) {
    }

    /**
     * Perform the draw for an event.
     *
     * @throws \Exception
     */
    public function performDraw(Event $event): Collection
    {
        // Check if draw was already done
        if ($event->status === 'draw_done' && $this->drawResultRepository->existsForEvent($event)) {
            throw new \Exception('O sorteio já foi realizado para este evento.');
        }

        // Get confirmed participants
        $participants = $this->participantRepository->getConfirmedByEvent($event);

        // Check minimum participants
        if ($participants->count() < 3) {
            throw new \Exception('É necessário pelo menos 3 participantes confirmados para realizar o sorteio.');
        }

        // Generate valid pairs
        $pairs = $this->generateValidPairs($participants);

        // Save draw results
        $drawResults = collect();
        foreach ($pairs as $pair) {
            $drawResult = $this->drawResultRepository->create(
                $event,
                $pair['giver'],
                $pair['receiver']
            );
            $drawResults->push($drawResult);
            $message = "Sorteio do evento *" . $event->title . " realizado*.\n\n";
            $message .= "Seu amigo secreto é *" . $pair['receiver']->name . "*.\n\n";
            $message .= "Confira a sugestão de presente do seu amigo secreto:\n\n";
            $message .= $pair['receiver']->gift_suggestion;
            (new WhatsApp())->sendMessageText($message, (string)$pair['giver']->whatsapp_number, 'Olá, *' . $pair['giver']->name . '*!', 2);
        }

        // Update event status
        $this->eventRepository->update($event, ['status' => 'draw_done']);

        return $drawResults->map(function ($result) {
            return [
                'giver' => [
                    'id' => $result->giver->id,
                    'name' => $result->giver->name,
                ],
                'receiver' => [
                    'id' => $result->receiver->id,
                    'name' => $result->receiver->name,
                ],
            ];
        });
    }

    /**
     * Generate valid pairs ensuring no one draws themselves.
     */
    protected function generateValidPairs(Collection $participants): array
    {
        $participantsArray = $participants->toArray();
        $count = count($participantsArray);
        $pairs = [];

        // Create a shuffled list of receivers
        $receivers = $participants->shuffle();
        $givers = $participants;

        // Try to create valid pairs
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $pairs = [];
            $usedReceivers = [];
            $valid = true;

            foreach ($givers as $giver) {
                $availableReceivers = $receivers->filter(function ($receiver) use ($giver, $usedReceivers) {
                    return $receiver->id !== $giver->id && !in_array($receiver->id, $usedReceivers);
                });

                if ($availableReceivers->isEmpty()) {
                    $valid = false;
                    break;
                }

                $receiver = $availableReceivers->random();
                $usedReceivers[] = $receiver->id;

                $pairs[] = [
                    'giver' => $giver,
                    'receiver' => $receiver,
                ];
            }

            if ($valid && count($pairs) === $count) {
                return $pairs;
            }

            $receivers = $receivers->shuffle();
            $attempt++;
        }

        // If we couldn't find a valid solution, use a simple algorithm
        return $this->generateSimplePairs($participants);
    }

    /**
     * Generate pairs using a simple circular algorithm.
     */
    protected function generateSimplePairs(Collection $participants): array
    {
        $participantsArray = $participants->values()->all();
        $count = count($participantsArray);
        $pairs = [];

        for ($i = 0; $i < $count; $i++) {
            $giver = $participantsArray[$i];
            $receiverIndex = ($i + 1) % $count;
            $receiver = $participantsArray[$receiverIndex];

            $pairs[] = [
                'giver' => $giver,
                'receiver' => $receiver,
            ];
        }

        return $pairs;
    }

    /**
     * Get draw results for an event.
     */
    public function getDrawResults(Event $event): Collection
    {
        $drawResults = $this->drawResultRepository->getByEvent($event);

        return $drawResults->map(function ($result) {
            return [
                'giver' => [
                    'id' => $result->giver->id,
                    'name' => $result->giver->name,
                ],
                'receiver' => [
                    'id' => $result->receiver->id,
                    'name' => $result->receiver->name,
                ],
            ];
        });
    }
}
