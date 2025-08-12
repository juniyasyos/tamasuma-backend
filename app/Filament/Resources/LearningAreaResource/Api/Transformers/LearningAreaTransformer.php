<?php
namespace App\Filament\Resources\LearningAreaResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\LearningArea;

/**
 * @property LearningArea $resource
 */
class LearningAreaTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
