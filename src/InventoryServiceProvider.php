<?php

declare(strict_types=1);

namespace Varsite\Inventory;

use Illuminate\Support\ServiceProvider;
use Varsite\Catalog\Contracts\StockProvider;
use Varsite\Inventory\Enums\MovementType;
use Varsite\Inventory\Services\StockLedger;
use Varsite\Platform\Capabilities\CapabilityRegistry;
use Varsite\Platform\Capabilities\Column;
use Varsite\Platform\Capabilities\Field;
use Varsite\Platform\Capabilities\Filter;
use Varsite\Platform\Capabilities\ResourceCapability;
use Varsite\Platform\Routing\ModuleRouteRegistrar;
use Varsite\Platform\Support\ModuleManager;

/**
 * Magazyn — pierwszy moduł dostarczający FAKT dla pojęcia należącego do innego
 * kontekstu.
 *
 * Zależność jest jednokierunkowa: magazyn zna kontrakt katalogu i go
 * implementuje, katalog o magazynie nie wie. Odinstalowanie magazynu przywraca
 * pustą implementację po stronie katalogu (stan nieznany), więc sklep działa
 * dalej — patrz DEPENDENCY-STRATEGY.md.
 */
final class InventoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->make(ModuleManager::class)->register(new InventoryModule());

        // Nadpisanie pustej implementacji katalogu prawdziwym źródłem faktu.
        $this->app->singleton(StockProvider::class, StockLedger::class);
        $this->app->singleton(StockLedger::class);

        $this->app->make(ModuleRouteRegistrar::class)
            ->register('inventory', require __DIR__.'/../routes/admin.php');

        $this->app->make(CapabilityRegistry::class)->register(
            ResourceCapability::make('inventory.movements')
                ->label('Ruch magazynowy', 'Ruchy magazynowe')
                ->icon('warehouse')
                ->endpoint('/v1/admin/inventory/movements')
                ->permission('inventory.view')
                ->columns([
                    Column::text('sku')->label('SKU')->sortable()->primary(),
                    Column::status('type', MovementType::tones())->label('Rodzaj'),
                    Column::number('quantity')->label('Ilość'),
                    Column::badge('location')->label('Lokalizacja'),
                    Column::date('created_at')->label('Data')->sortable(),
                ])
                ->filters([
                    Filter::search(['sku']),
                    Filter::segmented('type', ['all' => 'Wszystkie'] + MovementType::options()),
                ])
                ->form([
                    Field::text('sku')->label('SKU')->required()
                        ->hint('Identyfikator pozycji — ten sam co w katalogu.'),
                    Field::select('type', MovementType::options())->label('Rodzaj')->required(),
                    Field::number('quantity')->label('Ilość')->required()
                        ->hint('Wydanie zapisuje się jako wartość ujemna automatycznie.'),
                    Field::text('location')->label('Lokalizacja')->hint('Domyślnie „default”.'),
                    Field::text('reason')->label('Uzasadnienie'),
                ])
                // Bez akcji edycji i usuwania: rejestr jest append-only,
                // a pomyłkę prostuje się korektą, która zostaje w historii.
                ->actions([]),
        );
    }
}
