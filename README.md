# varsite/inventory

Magazyn dla Varsite Platform — **stany wyliczane z rejestru ruchów**.

```bash
composer require varsite/inventory
php artisan varsite:module install inventory
```

## Zasada: stan nie jest polem

Stan magazynowy **nie jest nigdzie zapisany** — to suma ruchów. Rejestr jest
append-only: ruchów się nie edytuje ani nie usuwa, a pomyłkę prostuje korekta,
która zostaje w historii.

Dzięki temu na pytanie „dlaczego stan to 3" zawsze istnieje odpowiedź, a przyszłe
rozszerzenia sprowadzają się do nowego rodzaju ruchu albo nowego wymiaru pozycji:

| Rozszerzenie | Co dochodzi |
|---|---|
| Wiele magazynów | wiersze z inną `location` (dyskryminator istnieje od początku) |
| Partie, numery seryjne | dodatkowy wymiar ruchu |
| Rezerwacje | rodzaj ruchu; stan dostępny = suma − rezerwacje |
| Inwentaryzacja | ruch typu `correction` z uzasadnieniem |
| Integracja z ERP | `source` i `external_id` w ruchu |

## Relacja z katalogiem

Magazyn implementuje kontrakt **należący do katalogu**
(`Varsite\Catalog\Contracts\StockProvider`), bo to katalog jest właścicielem
pojęcia „dostępność oferowanej pozycji"; magazyn dostarcza fakt.

Zależność jest jednokierunkowa: **magazyn zna katalog, katalog nie zna magazynu**.
Po `composer remove varsite/inventory` katalog wraca do pustej implementacji
i pokazuje stan jako `null` — nieznany, **nie zerowy**. Sklep działa dalej.

## Trzy różne fakty

| Fakt | Właściciel |
|---|---|
| czy oferujemy (`is_offered`) | Catalog |
| ile fizycznie mamy | Inventory |
| czy można kupić | Orders (wynik, nie dana) |

Rozróżnienie `null` od `0` jest domenowe: `null` znaczy „pojęcie nie dotyczy"
(produkt cyfrowy, usługa), `0` znaczy „nie ma towaru".

## API

```
GET  /api/v1/admin/inventory/movements     rejestr ruchów
POST /api/v1/admin/inventory/movements     zapis ruchu (jedyny sposób zmiany stanu)
GET  /api/v1/admin/inventory/stock/{sku}   stan z rozbiciem na lokalizacje
```
