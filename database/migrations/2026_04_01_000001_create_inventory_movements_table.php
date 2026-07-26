<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            // SKU jest identyfikatorem naturalnym dzielonym z katalogiem — nigdy
            // kluczem obcym: magazyn działa bez katalogu i odwrotnie.
            $table->string('sku')->index();
            // Dyskryminator lokalizacji od pierwszego dnia: wiele magazynów
            // później to nowe wiersze, nie zmiana struktury ani klucza.
            $table->string('location')->default('default')->index();
            $table->string('type');
            $table->integer('quantity');
            $table->string('reason')->nullable();
            // Ślad pochodzenia dla integracji zewnętrznych (ERP, marketplace).
            $table->string('source')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['sku', 'location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
