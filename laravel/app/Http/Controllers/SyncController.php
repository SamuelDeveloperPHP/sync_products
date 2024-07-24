<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SyncProductsJob;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    
    public function sync()
    {

        // Despachar o job com o usuário autenticado
        $user = Auth::user();
        SyncProductsJob::dispatch($user);
        
        return response()->json(['message' => 'Sincronização concuída com sucesso']);

    }
}
