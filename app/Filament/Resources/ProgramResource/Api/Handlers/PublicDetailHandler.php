<?php

namespace App\Filament\Resources\ProgramResource\Api\Handlers;

use App\Filament\Resources\ProgramResource;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class PublicDetailHandler extends Handlers
{
    public static ?string $uri = '/{id}';
    public static ?string $resource = ProgramResource::class;
    public static bool $public = true;

    /**
     * Show a published program
     *
     * @param Request $request
     * @return mixed
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');

        $record = static::getEloquentQuery()
            ->where('is_published', true)
            ->where(static::getKeyName(), $id)
            ->first();

        if (! $record) {
            return static::sendNotFoundResponse();
        }

        $transformer = static::getApiTransformer();

        return new $transformer($record);
    }
}
