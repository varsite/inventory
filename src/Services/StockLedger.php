<?php

declare(strict_types=1);

namespace Varsite\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Varsite\Catalog\Contracts\StockProvider;
use Varsite\Inventory\Enums\MovementType;
use Varsite\Inventory\Models\StockMovement;

/**
 * Stan magazynowy wyliczany z rejestru ruchów.
 *
 * Implementuje kontrakt katalogu (`StockProvider`), bo to katalog jest
 * właścicielem POJĘCIA dostępności; magazyn dostarcza FAKT. Kierunek zależności
 * jest jednokierunkowy: magazyn zna katalog, katalog nie zna magazynu.
 *
 * Stan nie jest nigdzie zapisany — to suma ruchów. Ta decyzja jest warunkiem
 * koniecznym przyszłych rozszerzeń: gdyby stan był edytowalny, historia
 * przestałaby zgadzać się z rzeczywistością i partie, rezerwacje czy
 * inwentaryzacje straciłyby podstawę.
 */
final class StockLedger implements StockProvider
{
    public function availableQuantity(string $sku): ?int
    {
        // Brak jakiegokolwiek ruchu = magazyn nie zna tej pozycji. To NIE jest
        // zero: produkt cyfrowy czy usługa nigdy nie trafią do magazynu, a mimo
        // to są dostępne. Rozróżnienie null/0 jest decyzją domenową.
        $movements = StockMovement::query()->where('sku', $sku);

        if (! $movements->exists()) {
            return null;
        }

        return (int) StockMovement::query()->where('sku', $sku)->sum('quantity');
    }

    /** Stan w rozbiciu na lokalizacje — podstawa pod wiele magazynów. */
    public function quantityByLocation(string $sku): array
    {
        return StockMovement::query()
            ->where('sku', $sku)
            ->groupBy('location')
            ->selectRaw('location, SUM(quantity) as quantity')
            ->pluck('quantity', 'location')
            ->map(static fn ($value): int => (int) $value)
            ->all();
    }

    /**
     * Zapis ruchu. Jedyny sposób zmiany stanu — nie istnieje metoda „ustaw stan".
     * Korekta po inwentaryzacji też jest ruchem i zostaje w historii.
     */
    public function record(
        string $sku,
        MovementType $type,
        int $quantity,
        string $location = 'default',
        ?string $reason = null,
        ?string $source = null,
        ?string $externalId = null,
    ): StockMovement {
        // Wydanie zapisujemy jako wartość ujemną, żeby stan był zwykłą sumą.
        $signed = $type === MovementType::Issue ? -abs($quantity) : $quantity;

        return DB::transaction(static fn (): StockMovement => StockMovement::create([
            'sku' => $sku,
            'location' => $location,
            'type' => $type->value,
            'quantity' => $signed,
            'reason' => $reason,
            'source' => $source,
            'external_id' => $externalId,
        ]));
    }
}
