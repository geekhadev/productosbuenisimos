<?php

namespace App\Support\Sii;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Extrae actividades económicas desde el HTML público del listado SII (códigos 1956–1959).
 *
 * @phpstan-type Row array{code: string, description: string, use_iva: bool, tax_category: string, use_internet: bool}
 */
final class SiiEconomicActivitiesHtmlParser
{
    private const CODE_PATTERN = '/^\d{6}$/';

    /**
     * @return list<Row>
     */
    public function parse(string $html): array
    {
        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $tables = $xpath->query("//table[contains(concat(' ', normalize-space(@class), ' '), ' table-bordered ')]");

        if ($tables === false || $tables->length === 0) {
            return [];
        }

        /** @var DOMElement $table */
        $table = $tables->item(0);
        $rows = $xpath->query('.//tr', $table);

        if ($rows === false) {
            return [];
        }

        /** @var list<Row> $activities */
        $activities = [];

        foreach ($rows as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }

            if ($this->isSectionHeaderRow($row)) {
                continue;
            }

            $cells = $this->rowTextCells($row);

            if (count($cells) !== 5) {
                continue;
            }

            if ($this->isSubsectionHeaderRow($cells)) {
                continue;
            }

            if (! preg_match(self::CODE_PATTERN, $cells[0])) {
                continue;
            }

            $activities[] = [
                'code' => $cells[0],
                'description' => $cells[1],
                'use_iva' => $this->parseSiColumn($cells[2]),
                'tax_category' => $cells[3],
                'use_internet' => $this->parseSiColumn($cells[4]),
            ];
        }

        return $activities;
    }

    private function isSectionHeaderRow(DOMElement $row): bool
    {
        foreach ($row->getElementsByTagName('th') as $th) {
            if ($th->getAttribute('colspan') !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $cells
     */
    private function isSubsectionHeaderRow(array $cells): bool
    {
        return mb_strtoupper(trim($cells[0])) === 'CÓDIGO';
    }

    /**
     * @return list<string>
     */
    private function rowTextCells(DOMElement $row): array
    {
        $cells = [];

        foreach ($row->getElementsByTagName('td') as $td) {
            if (! $td instanceof DOMElement) {
                continue;
            }

            $cells[] = $this->normalizeText($this->elementText($td));
        }

        return $cells;
    }

    private function elementText(DOMNode $node): string
    {
        return $node->textContent ?? '';
    }

    private function normalizeText(string $value): string
    {
        $trimmed = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return (string) preg_replace('/\s+/u', ' ', $trimmed);
    }

    private function parseSiColumn(string $value): bool
    {
        return mb_strtoupper(trim($value)) === 'SI';
    }
}
