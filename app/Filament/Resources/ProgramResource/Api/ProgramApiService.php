<?php

namespace App\Filament\Resources\ProgramResource\Api;

use App\Filament\Resources\ProgramResource;
use Rupadana\ApiService\ApiService;

class ProgramApiService extends ApiService
{
    protected static ?string $resource = ProgramResource::class;
    protected static ?string $groupRouteName = 'programs';

    public static function handlers(): array
    {
        return [
            Handlers\PublicPaginationHandler::class,
            Handlers\PublicDetailHandler::class,
            Handlers\EnrolmentHandler::class,
        ];
    }
}
