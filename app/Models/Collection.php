<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $connection = 'mysqlCollection';
    protected $fillable = [
        'organization_id',
        'storage_type',
        'collection_name',
        'collection_description',
        'published_collection_name',
        'is_synced',
        'is_cloud_collection'
    ];

    // Define the relationship to CollectionData
    public function collectionData()
    {
        return $this->hasMany(CollectionData::class, 'collection_id');
    }
}
