<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LearningAreaResource as LearningAreaApiResource;
use App\Http\Resources\ProgramResource as ProgramApiResource;
use App\Models\LearningArea;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LearningAreaController extends Controller
{
    /**
     * GET /api/v1/learning-areas
     * List learning areas with optional programs include.
     */
    public function index(Request $request)
    {
        $includePrograms = str_contains((string) $request->query('include'), 'programs');
        $onlyActive = filter_var($request->query('active', 'true'), FILTER_VALIDATE_BOOLEAN);

        $areas = LearningArea::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->withCount(['programs' => fn ($q) => $q->when(true, fn ($qq) => $qq->where('is_published', true))])
            ->when($includePrograms, function ($q) {
                $q->with(['programs' => function ($p) {
                    $p->where('is_published', true)
                        ->orderByDesc('created_at');
                }]);
            })
            ->orderBy('name')
            ->paginate(perPage: (int) $request->query('per_page', 15));

        return LearningAreaApiResource::collection($areas);
    }

    /**
     * GET /api/v1/learning-areas/{learningArea}
     * Show a single learning area, with programs included by default.
     */
    public function show(Request $request, LearningArea $learningArea)
    {
        $learningArea->load(['programs' => fn ($q) => $q->where('is_published', true)->latest()]);

        return new LearningAreaApiResource($learningArea);
    }

    /**
     * GET /api/v1/learning-areas/{learningArea}/programs
     * List programs for an area.
     */
    public function programs(Request $request, LearningArea $learningArea)
    {
        $programs = Program::query()
            ->where('learning_area_id', $learningArea->getKey())
            ->where('is_published', true)
            ->when($request->boolean('certified'), fn ($q) => $q->where('is_certified', true))
            ->orderByDesc('created_at')
            ->paginate(perPage: (int) $request->query('per_page', 15));

        return ProgramApiResource::collection($programs);
    }
}

