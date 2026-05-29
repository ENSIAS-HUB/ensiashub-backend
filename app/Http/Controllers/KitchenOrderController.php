<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Order;

class KitchenOrderController extends Controller
{
    /**
     * GET /api/kitchen/orders
     * Retourne toutes les commandes actives (hors completed/cancelled par défaut).
     * Filtres : ?status=preparing  ?date=2026-05-20  ?search=Yasser
     */
    public function index(Request $request)
    {
        $query = Order::with(['lines.menuItem', 'user'])
            ->orderByRaw("CASE statut
                WHEN 'EnAttente'     THEN 1
                WHEN 'Confirme'      THEN 2
                WHEN 'EnPreparation' THEN 3
                WHEN 'Prete'         THEN 4
                WHEN 'Recuperee'     THEN 5
                WHEN 'Annulee'       THEN 6
                ELSE 7 END")
            ->orderBy('created_at', 'asc');

        // Filter by status
        if ($request->filled('status')) {
            $statut = Order::statusToStatut($request->input('status'));
            $query->where('statut', $statut);
        } else {
            // Default: exclude completed and cancelled
            $query->whereNotIn('statut', ['Recuperee', 'Annulee']);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // Search by customer name or order number
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('numeroCommande', 'ilike', $search)
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nom', 'ilike', $search)
                         ->orWhere('prenom', 'ilike', $search);
                  });
            });
        }

        $orders = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $orders->map(fn ($o) => $o->toKitchenArray()),
        ]);
    }

    /**
     * GET /api/kitchen/orders/{order}
     * Détail complet d'une commande.
     */
    public function show(Order $order)
    {
        $order->load(['lines.menuItem', 'user']);

        return response()->json([
            'success' => true,
            'data'    => $order->toKitchenArray(),
        ]);
    }

    /**
     * PATCH /api/kitchen/orders/{order}/status
     * Body: { "status": "preparing" }
     */
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,ready,completed,cancelled',
        ]);

        $newStatus = $data['status'];

        if (!$order->canTransitionTo($newStatus)) {
            $current = Order::statutToStatus($order->statut);
            return response()->json([
                'success' => false,
                'error'   => "Transition invalide : '{$current}' → '{$newStatus}'",
                'allowed' => Order::allowedTransitions()[$current] ?? [],
            ], 422);
        }

        $order->update(['statut' => Order::statusToStatut($newStatus)]);

        $order->load(['lines.menuItem', 'user']);

        return response()->json([
            'success' => true,
            'data'    => $order->toKitchenArray(),
        ]);
    }

    /**
     * GET /api/kitchen/stats
     * Compteurs par statut pour le dashboard.
     */
    public function stats()
    {
        $today = now()->toDateString();

        $counts = Order::selectRaw("
            SUM(CASE WHEN statut = 'EnAttente'     THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN statut = 'Confirme'      THEN 1 ELSE 0 END) AS confirmed,
            SUM(CASE WHEN statut = 'EnPreparation' THEN 1 ELSE 0 END) AS preparing,
            SUM(CASE WHEN statut = 'Prete'         THEN 1 ELSE 0 END) AS ready,
            SUM(CASE WHEN statut = 'Recuperee' AND DATE(created_at) = :today1 THEN 1 ELSE 0 END) AS completed_today,
            SUM(CASE WHEN statut = 'Annulee'   AND DATE(created_at) = :today2 THEN 1 ELSE 0 END) AS cancelled_today
        ", ['today1' => $today, 'today2' => $today])
        ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'pending'         => (int) ($counts->pending ?? 0),
                'confirmed'       => (int) ($counts->confirmed ?? 0),
                'preparing'       => (int) ($counts->preparing ?? 0),
                'ready'           => (int) ($counts->ready ?? 0),
                'completed_today' => (int) ($counts->completed_today ?? 0),
                'cancelled_today' => (int) ($counts->cancelled_today ?? 0),
            ],
        ]);
    }
}
