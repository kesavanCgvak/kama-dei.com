<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionData extends Model
{
    use HasFactory;

    protected $table = 'collection_data'; // Specify the table name if it doesn't follow the default naming convention.

    protected $fillable = [
        'collection_id',
        'file_name',
        'size',
        'created_date',
    ];

    // Define the relationship to Collection
    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }
}
