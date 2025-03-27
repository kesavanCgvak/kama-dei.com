<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'storage_type',
        'collection_name',
    ];

    // Define the relationship to CollectionData
    public function collectionData()
    {
        return $this->hasMany(CollectionData::class, 'collection_id');
    }
}
