<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionDataResource extends JsonResource
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
            'collection_id' => $this->collection_id,
            'file_name' => $this->file_name,
            'size' => $this->size,
            'file_id' => $this->file_id,
            'bucket_sp_site_name' => $this->bucket_sp_site_name,
            'last_modified' => $this->last_modified,
            'last_modified_readable' => Carbon::parse($this->last_modified)->format('m-d-Y H:i')
        ];
    }
}
