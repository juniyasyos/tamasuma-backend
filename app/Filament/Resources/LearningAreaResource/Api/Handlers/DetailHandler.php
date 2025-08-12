<?php

namespace App\Filament\Resources\LearningAreaResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\LearningAreaResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\LearningAreaResource\Api\Transformers\LearningAreaTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = LearningAreaResource::class;


    /**
     * Show LearningArea
     *
     * @param Request $request
     * @return LearningAreaTransformer
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');
        
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )
            ->first();

        if (!$query) return static::sendNotFoundResponse();

        return new LearningAreaTransformer($query);
    }
}
