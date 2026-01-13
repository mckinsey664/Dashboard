<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:clients,name'],
            'region' => ['nullable', 'string', 'max:100'],
            'email'  => ['nullable', 'email', 'max:255'],
        ]);

        $client = Client::create($data);

        return response()->json([
            'message' => 'Client created',
            'client'  => ['id' => $client->id, 'name' => $client->name],
        ], 201);
    }
}
