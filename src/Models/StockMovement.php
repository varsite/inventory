<?php

declare(strict_types=1);

namespace Varsite\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Varsite\Inventory\Enums\MovementType;

/**
 * Ruch magazynowy — jedyne źródło prawdy o stanie.
 *
 * Rejestr jest APPEND-ONLY: ruchów się nie edytuje ani nie usuwa. Pomyłkę
 * prostuje się korektą, która zostaje w historii. Dzięki temu na pytanie
 * „dlaczego stan to 3" zawsze istnieje odpowiedź, a przyszłe rozszerzenia
 * (partie, numery seryjne, rezerwacje, integracje ERP) sprowadzają się do
 * nowych rodzajów ruchu albo dodatkowych wymiarów — nie do przebudowy.
 *
 * `quantity` jest liczbą ze znakiem: przyjęcie dodatnie, wydanie ujemne.
 * Stan to suma ruchów, nigdy pole zapisane ręcznie.
 */
final class StockMovement extends Model
{
    public const UPDATED_AT = null; // ruch jest niezmienny

    protected $table = 'inventory_movements';

    protected $fillable = ['sku', 'location', 'type', 'quantity', 'reason', 'source', 'external_id'];

    protected $casts = [
        'type' => MovementType::class,
        'quantity' => 'integer',
    ];
}
