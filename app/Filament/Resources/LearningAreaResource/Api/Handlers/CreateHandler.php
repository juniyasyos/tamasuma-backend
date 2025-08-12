<?php
namespace App\Filament\Resources\LearningAreaResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\LearningAreaResource;
use App\Filament\Resources\LearningAreaResource\Api\Requests\CreateLearningAreaRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = LearningAreaResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create LearningArea
     *
     * @param CreateLearningAreaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateLearningAreaRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}