<?php

namespace App\Filament\Resources\ProgramResource\Api\Handlers;

use App\Filament\Resources\ProgramResource;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class PublicPaginationHandler extends Handlers
{
    public static ?string $uri = '/';
    public static ?string $resource = ProgramResource::class;
    public static bool $public = true;

    /**
     * List published programs
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function handler()
    {
        $query = static::getEloquentQuery()->where('is_published', true);

        $query = QueryBuilder::for($query)
            ->allowedFields($this->getAllowedFields() ?? [])
            ->allowedSorts($this->getAllowedSorts() ?? [])
            ->allowedFilters($this->getAllowedFilters() ?? [])
            ->allowedIncludes($this->getAllowedIncludes() ?? [])
            ->paginate(request()->query('per_page'))
            ->appends(request()->query());

        $transformer = static::getApiTransformer();

        return $transformer::collection($query);
    }
}
