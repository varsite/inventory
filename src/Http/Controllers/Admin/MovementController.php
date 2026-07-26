<?php

declare(strict_types=1);

namespace Varsite\Inventory\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Varsite\Inventory\Enums\MovementType;
use Varsite\Inventory\Models\StockMovement;
use Varsite\Inventory\Services\StockLedger;

/**
 * Rejestr ruchów w panelu.
 *
 * Celowo BEZ edycji i usuwania: rejestr jest append-only, a pomyłkę prostuje
 * się korektą. To nie ograniczenie interfejsu, tylko odwzorowanie reguły domeny.
 */
final class MovementController
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('inventory.view');

        $movements = StockMovement::query()
            ->when($request->string('q')->toString() !== '', fn ($q) => $q->where('sku', 'like', '%'.$request->string('q')->toString().'%'))
            ->when($request->string('type')->toString() !== '', fn ($q) => $q->where('type', $request->string('type')->toString()))
            ->when($request->string('location')->toString() !== '', fn ($q) => $q->where('location', $request->string('location')->toString()))
            ->latest('id')->paginate(30);

        return response()->json($movements);
    }

    public function store(Request $request, StockLedger $ledger): JsonResponse
    {
        Gate::authorize('inventory.record');

        $data = $request->validate([
            'sku' => ['required', 'string', 'max:64'],
            'type' => ['required', new Enum(MovementType::class)],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'location' => ['nullable', 'string', 'max:64'],
            'reason' => ['nullable', 'string', 'max:190'],
        ]);

        $movement = $ledger->record(
            sku: $data['sku'],
            type: MovementType::from($data['type']),
            quantity: (int) $data['quantity'],
            location: $data['location'] ?? 'default',
            reason: $data['reason'] ?? null,
        );

        return response()->json(['data' => $movement], 201);
    }

    /** Aktualny stan pozycji w rozbiciu na lokalizacje. */
    public function stock(string $sku, StockLedger $ledger): JsonResponse
    {
        Gate::authorize('inventory.view');

        return response()->json(['data' => [
            'sku' => $sku,
            'quantity' => $ledger->availableQuantity($sku),
            'locations' => $ledger->quantityByLocation($sku),
        ]]);
    }
}
