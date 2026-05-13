<?php

namespace App\Http\Requests\Concerns;

use Carbon\CarbonImmutable;

trait InteractsWithPaginatedListQuery
{
    /**
     * Normaliza query params comunes de listados paginados (sin campos de dominio como `status`).
     * Fusionar en `prepareForValidation()` del Form Request antes de validar.
     */
    protected function prepareStandardListQuery(): void
    {
        $perPage = (int) $this->input('per_page', 20);
        $perPage = max(5, min(100, $perPage));

        $search = $this->input('search');

        $this->merge([
            'sort' => $this->input('sort', 'created_at'),
            'direction' => $this->input('direction', 'desc'),
            'per_page' => $perPage,
            'search' => is_string($search) && $search === '' ? null : $search,
        ]);
    }

    /**
     * Filtros estándar para rehidratar la UI (search, sort, direction, per_page).
     *
     * @return array{search: mixed, sort: string, direction: string, per_page: int}
     */
    protected function standardListFiltersForFrontend(): array
    {
        return [
            'search' => $this->input('search'),
            'sort' => $this->input('sort', 'created_at'),
            'direction' => $this->input('direction', 'desc'),
            'per_page' => (int) $this->input('per_page', 20),
        ];
    }

    /**
     * Parte estándar de `filtersForAction` a partir del array validado.
     *
     * @param  array<string, mixed>  $validated
     * @return array{search: mixed, sort: string, direction: string, per_page: int}
     */
    protected function standardListFiltersForAction(array $validated): array
    {
        return [
            'search' => $validated['search'] ?? null,
            'sort' => $validated['sort'] ?? 'created_at',
            'direction' => $validated['direction'] ?? 'desc',
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ];
    }

    /**
     * Normaliza un valor de fecha de query a inicio o fin de día en la app (CarbonImmutable).
     *
     * @param  mixed  $value  Valor validado (p. ej. `date` rule)
     */
    protected function normalizeQueryDate(mixed $value, bool $endOfDay = false): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $parsed = CarbonImmutable::parse($value);

        return $endOfDay
            ? $parsed->endOfDay()->toDateTimeString()
            : $parsed->startOfDay()->toDateTimeString();
    }
}
