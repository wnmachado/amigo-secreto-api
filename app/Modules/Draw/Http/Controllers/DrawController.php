<?php

namespace App\Modules\Draw\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Events\Models\Event;
use App\Modules\Draw\Services\DrawService;
use Illuminate\Http\JsonResponse;

class DrawController extends Controller
{
    public function __construct(
        private DrawService $drawService
    ) {
    }

    /**
     * Perform the draw for an event.
     */
    public function draw(string $uuid): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $event);

        try {
            $pairs = $this->drawService->performDraw($event);

            return response()->json($pairs);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get draw results for an event.
     */
    public function results(string $uuid): JsonResponse
    {
        $event = \App\Modules\Events\Models\Event::where('uuid', $uuid)->firstOrFail();

        $this->authorize('view', $event);

        $results = $this->drawService->getDrawResults($event);

        return response()->json($results);
    }
}
