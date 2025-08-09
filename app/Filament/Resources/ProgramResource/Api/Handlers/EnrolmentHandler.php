<?php

namespace App\Filament\Resources\ProgramResource\Api\Handlers;

use App\Filament\Resources\ProgramResource;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class EnrolmentHandler extends Handlers
{
    public static ?string $uri = '/{id}/enrolments';
    public static string $method = 'post';
    public static ?string $resource = ProgramResource::class;
    public static bool $public = false;

    public static function getRouteMiddleware(): array
    {
        return ['auth:sanctum'];
    }

    public function handler(Request $request)
    {
        return response()->json([
            'message' => 'Enrolment endpoint'
        ]);
    }
}
