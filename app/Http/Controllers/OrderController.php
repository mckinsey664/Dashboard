<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    // POST /orders
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'    => ['required', 'exists:clients,id'],
            'order_code'   => ['required', 'string', 'max:255', 'unique:orders,order_code'],
            'overall_code' => ['nullable', 'string', 'max:255'],
            'inquiry_mail' => ['nullable', 'string'],
            'region'       => ['nullable', 'string', 'max:100'],
            'date_received'=> ['nullable', 'date'],
            'sent_to_client' => ['nullable', 'string', 'max:50'],
            'notes_to_purchasing' => ['nullable', 'string'],
            'notes_to_elias'     => ['nullable', 'string'],
            'ref'          => ['required', 'string', 'max:255', 'unique:orders,ref'],
            'priority'     => ['nullable', 'string', 'max:50'],

            'items'                 => ['array'],
            'items.*.part_number'   => ['nullable', 'string', 'max:255'],
            'items.*.manufacturer'  => ['nullable', 'string', 'max:255'],
            'items.*.quantity'      => ['nullable', 'numeric'],
            'items.*.uom'           => ['nullable', 'string', 'max:50'],
            'items.*.target_price_usd' => ['nullable', 'numeric'],
            'items.*.item_notes'    => ['nullable', 'string'],
        ]);

        $order = Order::create($data);

        if (!empty($data['items'])) {
            $items = collect($data['items'])->map(fn($i) => array_merge($i, ['order_id' => $order->id]))->all();
            OrderItem::insert($items);
        }

        // Eager load for response
        $order->load(['client', 'items']);

        // return response()->json([
        //     'message' => 'Order created successfully',
        //     'order'   => $order,
        // ], 201);
        return redirect()
    ->route('orders.show', $order)
    ->with('status', 'Order created successfully!');

    }

    // GET /orders/{order}
    public function show(Order $order)
    {
        $order->load(['client', 'items']);
        return response()->json($order);
    }
public function index()
{
    $orders = \App\Models\Order::with('client')->latest()->paginate(10);
    return view('orders.index', compact('orders'));
}



public function create()
{
    $clients = \App\Models\Client::orderBy('name')->get(['id','name']);
    return view('orders.create', compact('clients'));
}

}
