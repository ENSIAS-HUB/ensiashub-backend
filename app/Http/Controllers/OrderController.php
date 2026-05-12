<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\MenuItem;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * Returns the authenticated user's orders (most recent first).
     */
    public function index(Request $request)
    {
        $orders = Order::with(['lines.menuItem'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders->map(fn ($o) => $o->toApiArray()),
        ]);
    }

    /**
     * POST /api/orders
     * Body: { items: [{ mealId, quantity }], notes? }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.mealId' => 'required|uuid|exists:menu_items,id',
            'items.*.quantity'=> 'required|integer|min:1',
            'notes'          => 'nullable|string|max:500',
        ]);

        $total = 0;
        $lineData = [];

        foreach ($data['items'] as $item) {
            $meal = MenuItem::findOrFail($item['mealId']);
            $qty  = $item['quantity'];
            $unitPrice = $meal->prix;
            $lineTotal = round($unitPrice * $qty, 2);
            $total += $lineTotal;

            $lineData[] = [
                'menu_item_id' => $meal->id,
                'quantite'     => $qty,
                'prixUnitaire' => $unitPrice,
                'totalLigne'   => $lineTotal,
            ];
        }

        $order = Order::create([
            'user_id'        => $request->user()->id,
            'numeroCommande' => 'CMD-' . strtoupper(Str::random(8)),
            'statut'         => 'EnAttente',
            'total'          => round($total, 2),
            'notes'          => $data['notes'] ?? null,
        ]);

        foreach ($lineData as $line) {
            $order->lines()->create($line);
        }

        $order->load('lines.menuItem');

        return response()->json([
            'success' => true,
            'data'    => $order->toApiArray(),
        ], 201);
    }

    /**
     * GET /api/orders/{id}
     */
    public function show(Request $request, string $id)
    {
        $order = Order::with(['lines.menuItem'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order->toApiArray()]);
    }

    /**
     * PATCH /api/orders/{id}/cancel
     * Cancels an order that is still 'EnAttente'.
     */
    public function cancel(Request $request, string $id)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        if ($order->statut !== 'EnAttente') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'annuler une commande déjà en cours.',
            ], 422);
        }

        $order->update(['statut' => 'Annulee']);
        $order->load('lines.menuItem');

        return response()->json(['success' => true, 'data' => $order->toApiArray()]);
    }

    /**
     * PUT /api/orders/{id}  — Admin: update status
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $data = $request->validate([
            'statut' => 'required|in:EnAttente,EnPreparation,Prete,Recuperee,Annulee',
        ]);

        $order->update($data);
        $order->load('lines.menuItem');

        return response()->json(['success' => true, 'data' => $order->toApiArray()]);
    }

    /**
     * DELETE /api/orders/{id}  — Admin: delete
     */
    public function destroy(string $id)
    {
        Order::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Commande supprimée.']);
    }
}
