<?php

declare(strict_types=1);

namespace Varsite\Inventory\Enums;

/**
 * Rodzaj ruchu magazynowego.
 *
 * Rejestr jest append-only, więc rozszerzanie modelu o rezerwacje, zwroty czy
 * inwentaryzacje sprowadza się do nowego rodzaju ruchu — nigdy do zmiany
 * istniejących danych.
 */
enum MovementType: string
{
    case Receipt = 'receipt';       // przyjęcie
    case Issue = 'issue';           // wydanie
    case Correction = 'correction'; // korekta, np. po inwentaryzacji

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::Receipt->value => 'Przyjęcie',
            self::Issue->value => 'Wydanie',
            self::Correction->value => 'Korekta',
        ];
    }

    /** @return array<string, array{tone: string, label: string}> */
    public static function tones(): array
    {
        return [
            self::Receipt->value => ['tone' => 'ok', 'label' => 'Przyjęcie'],
            self::Issue->value => ['tone' => 'warn', 'label' => 'Wydanie'],
            self::Correction->value => ['tone' => 'muted', 'label' => 'Korekta'],
        ];
    }
}
