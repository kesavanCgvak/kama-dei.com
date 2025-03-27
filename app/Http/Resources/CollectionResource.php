<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'storage_type' => $this->storage_type,
            'collection_name' => $this->collection_name,
            'collection_description' => $this->collection_description,
            'published_collection_name' => $this->published_collection_name,
            'user_id' => $this->user_id,
            'is_synced' => $this->is_synced,
            'is_cloud_collection' => $this->is_cloud_collection,
            'collection_data' => CollectionDataResource::collection($this->whenLoaded('collectionData')),
        ];
    }
}
