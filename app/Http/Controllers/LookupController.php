<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'users' => User::query()
                    ->select(['id', 'name', 'email'])
                    ->orderBy('email')
                    ->get(),
                'cryptocurrencies' => Cryptocurrency::query()
                    ->select(['id', 'code', 'name', 'is_active'])
                    ->orderBy('code')
                    ->get(),
            ],
        ]);
    }
}

