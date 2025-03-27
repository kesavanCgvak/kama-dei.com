<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\CollectionData;
use Illuminate\Http\Request;
use App\Traits\AuditLoggable;

class CollectionLogController extends Controller
{
    public function index()
    {
        return [];
    }
}
