<?php

namespace App\Http\Controllers;

use App\Jobs\SyncProductsJob;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    
    public function sync()
    {

        SyncProductsJob::dispatch();
        
        return response()->json(['message' => 'Sincronização Concuída']);

    }
}
