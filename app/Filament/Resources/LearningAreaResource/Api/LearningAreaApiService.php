<?php
namespace App\Filament\Resources\LearningAreaResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\LearningAreaResource;
use Illuminate\Routing\Router;


class LearningAreaApiService extends ApiService
{
    protected static string | null $resource = LearningAreaResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
