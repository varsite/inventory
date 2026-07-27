<?php

declare(strict_types=1);

namespace Varsite\Inventory;

use Varsite\Platform\Contracts\ModuleManifest;
use Varsite\Platform\Contracts\PlatformModule;

final class InventoryModule implements PlatformModule
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            key: 'inventory',
            name: 'Magazyn',
            version: '1.0.0',
            description: 'Stany magazynowe wyliczane z rejestru ruchów.',
            author: 'Varsite',
            section: 'sales',
            icon: 'warehouse',
            order: 20,
            permissions: [
                'inventory.view',
                'inventory.record',
            ],
            requiresGeneration: '^0.6',
        );
    }
}
