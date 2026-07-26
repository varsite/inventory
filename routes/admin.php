<?php

declare(strict_types=1);

use Varsite\Inventory\Http\Controllers\Admin\MovementController;
use Varsite\Platform\Routing\ScopedRoutes;

return static function (ScopedRoutes $r): void {
    $r->middleware(['auth:sanctum'])->prefix('api/v1/admin/inventory')->group(function (ScopedRoutes $r): void {
        $r->get('movements', [MovementController::class, 'index']);
        $r->post('movements', [MovementController::class, 'store']);
        $r->get('stock/{sku}', [MovementController::class, 'stock']);
    });
};
