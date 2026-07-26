<?php

declare(strict_types=1);

namespace Varsite\Inventory;

use Varsite\Platform\Contracts\PlatformModule;

final class InventoryModule implements PlatformModule
{
    public function key(): string
    {
        return 'inventory';
    }

    public function label(): string
    {
        return 'Magazyn';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    /** @return array<int, string> */
    public function permissions(): array
    {
        return ['inventory.view', 'inventory.record'];
    }
}
