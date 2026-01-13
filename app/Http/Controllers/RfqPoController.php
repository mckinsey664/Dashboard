<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Storage;

class RfqPoController extends Controller
{
    public function rfqPo()
    {
        // Sheet IDs
        $masterId  = '1DWxMnzTqCNaz9xTkQYcE0jDjukpD_9nnUogz5Cd8SLA'; // Priced Items info + extended RFQs
        $secondId  = '14SRB6tiCcr-UddLCGO3KLWuWNwqHGo3jQWLsg58Nzhw'; // Limited + Cards

        // Ranges (IDs) - kept for master side
        $rangePricedItems = "'Priced Items info'!B:B";
        $rangeExtendedRFQ = "'extended RFQs'!B:B";
        $rangePOs         = "'Limited'!B3:B"; // (kept but no longer used directly)

        // Ranges (items) - kept for master side
        $rangePricedItemsItems = "'Priced Items info'!A:A";
        $rangeExtendedRFQItems = "'extended RFQs'!A:A";
        $rangePOsItems         = "'Limited'!A3:A"; // (kept but no longer used directly)

        // Ranges (VOLUME $) - kept for master side
        $rangeQuotedVolume1 = "'Priced Items info'!S:S";  // money
        $rangeQuotedVolume2 = "'extended RFQs'!S:S";      // money
        $rangePOVolume      = "'Limited'!T3:T";           // (kept but no longer used directly)

        // Tabs to combine in the POs spreadsheet (secondId)
        // If your second tab is actually named "TestCards", use ['Limited','TestCards'].
        $poTabs = ['Limited', 'Cards'];

        // Google Sheets client
        $client = $this->getOAuthClient();
        $sheets = new Sheets($client);

        // ---- RFQ & PO (by id) ----
        $pricedCount   = $this->countUniqueColumn($sheets, $masterId, $rangePricedItems, true);
        $extendedCount = $this->countUniqueColumn($sheets, $masterId, $rangeExtendedRFQ, true);
        $quotedCount   = $pricedCount + $extendedCount;

        // Aggregate across Limited + Cards (column B, starting row 3)
        $poCount = $this->countUniqueAcrossTabs($sheets, $secondId, $poTabs, 'B', false, 3);
        $conversion = $quotedCount > 0 ? round(($poCount / $quotedCount) * 100, 1) : 0.0;

        // ---- Items (by row) ----
        $pricedCountItems   = $this->countUniqueColumn($sheets, $masterId, $rangePricedItemsItems, true);
        $extendedCountItems = $this->countUniqueColumn($sheets, $masterId, $rangeExtendedRFQItems, true);
        $quotedCountItems   = $pricedCountItems + $extendedCountItems;

        // Aggregate across Limited + Cards (column L, starting row 3)
        //$poCountItems   = $this->countUniqueAcrossTabs($sheets, $secondId, $poTabs, 'A', false, 3);
        // AFTER (plain count of non-empty cells; trims spaces)
        $poCountItems = $this->countFilledAcrossTabs($sheets, $secondId, $poTabs, 'L', 3);

        $conversionItems = $quotedCountItems > 0 ? round(($poCountItems / $quotedCountItems) * 100, 1) : 0.0;

        // ---- Volume ($) ----
        $quotedVolume = $this->sumNumericColumn($sheets, $masterId, $rangeQuotedVolume1, true)
                        + $this->sumNumericColumn($sheets, $masterId, $rangeQuotedVolume2, true);

        // Aggregate across Limited + Cards (column T, starting row 3)
        $poVolume = $this->sumNumericAcrossTabs($sheets, $secondId, $poTabs, 'T', false, 3);
        $conversionVolume = $quotedVolume > 0 ? round(($poVolume / $quotedVolume) * 100, 1) : 0.0;

        /* ---- Top 20 by RFQ volume ($) — Priced Items info + extended RFQs (E=client, S=value) ---- */
        $top20Rfq = [];
        try {
            $rfqByClient = $this->rfqTotalsFromTabs($sheets, $masterId, [
                'Priced Items info',
                'extended RFQs',
            ]);
            arsort($rfqByClient, SORT_NUMERIC);
            $rank = 0;
            foreach ($rfqByClient as $name => $total) {
                $top20Rfq[] = ['client' => $name, 'rfq_total' => $total];
                if (++$rank >= 20) break;
            }
        } catch (\Throwable $e) {
            $top20Rfq = []; // fail safe
        }

        // Max for bar scaling (avoid divide-by-zero)
        $maxRfqTotal = 0.0;
        foreach ($top20Rfq as $r) if ($r['rfq_total'] > $maxRfqTotal) $maxRfqTotal = $r['rfq_total'];
        if ($maxRfqTotal <= 0) $maxRfqTotal = 1;

        /* ---- Top 20 PO volume ($) — combine Limited + Cards (F=client, T=value) ---- */
        $top20Po = [];
        try {
            $poByClient = $this->totalsByKeyAcrossTabs(
                $sheets,
                $secondId,
                $poTabs,   // ['Limited','Cards']
                'F',       // key column (client)
                'T',       // value column (amount)
                3          // start row (skip headers)
            );

            arsort($poByClient, SORT_NUMERIC);
            $rank = 0;
            foreach ($poByClient as $name => $total) {
                $top20Po[] = ['client' => $name, 'po_total' => $total];
                if (++$rank >= 20) break;
            }
        } catch (\Throwable $e) {
            $top20Po = [];
        }

        $maxPoTotal     = 0.0;
        foreach ($top20Po as $r) if ($r['po_total'] > $maxPoTotal) $maxPoTotal = $r['po_total'];
        if ($maxPoTotal <= 0) $maxPoTotal = 1;
        $topPoClient    = $top20Po[0] ?? null;
        $bottomPoClient = $top20Po ? $top20Po[count($top20Po)-1] : null;

        return view('rfq-to-po', compact(
            'quotedCount', 'poCount', 'conversion',
            'quotedCountItems', 'poCountItems', 'conversionItems',
            'quotedVolume', 'poVolume', 'conversionVolume',
            'top20Rfq','maxRfqTotal',
            'top20Po','maxPoTotal','topPoClient','bottomPoClient'
        ));
    }

    private function countUniqueColumn(Sheets $sheets, string $spreadsheetId, string $range, bool $skipHeader = true): int
    {
        $resp = $sheets->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $resp->getValues() ?? [];

        $values = [];
        foreach ($rows as $r) {
            $cell = isset($r[0]) ? trim((string)$r[0]) : '';
            if ($cell !== '') $values[] = $cell;
        }
        if ($skipHeader && count($values) > 0) array_shift($values);

        return count(array_unique($values));
    }

    /**
     * Sum a single-column numeric range. Accepts values like "USD 1,234.56", "$1,234", "1 234,56",
     * parentheses for negatives, etc. Non-numeric rows are ignored. Optionally skip header.
     */
    private function sumNumericColumn(Sheets $sheets, string $spreadsheetId, string $range, bool $skipHeader = true): float
    {
        $resp = $sheets->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $resp->getValues() ?? [];

        $sum = 0.0;
        $start = 0;

        if ($skipHeader && !empty($rows)) {
            $start = 1; // force-skip first row as header
        }

        for ($i = $start; $i < count($rows); $i++) {
            $raw = isset($rows[$i][0]) ? (string)$rows[$i][0] : '';
            if ($raw === '') continue;

            $v = trim($raw);
            $neg = false;
            if (preg_match('/^\(.*\)$/', $v)) { $neg = true; $v = trim($v, '()'); }

            $v = str_replace(['USD', 'usd', '$', '€', '£', ' ', "\u{00A0}"], '', $v);
            if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $v)) {
                $v = str_replace('.', '', $v);
                $v = str_replace(',', '.', $v);
            } else {
                $v = str_replace(',', '', $v);
            }

            if (is_numeric($v)) {
                $num = (float)$v;
                if ($neg) $num = -$num;
                $sum += $num;
            }
        }

        return $sum;
    }

    private function getOAuthClient(): Client
    {
        $client = new Client();
        $client->setApplicationName('Laravel Google Sheets');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(Storage::path('google/credentials.json'));
        $client->setAccessType('offline');

        $tokenPath = Storage::path('google/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
        }
        return $client;
    }

    private function parseMoney(?string $raw): ?float
    {
        if ($raw === null) return null;
        $v = trim($raw);
        if ($v === '' || strtoupper($v) === 'FOC') return null;

        $neg = false;
        if (preg_match('/^\(.*\)$/', $v)) { $neg = true; $v = trim($v, '()'); }

        $v = str_replace(['USD','usd','$','€','£',' ', "\xC2\xA0"], '', $v);
        if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $v)) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else {
            $v = str_replace(',', '', $v);
        }

        if (!is_numeric($v)) return null;
        $n = (float)$v;
        return $neg ? -$n : $n;
    }

    /**
     * Sum column S by client (column E) across multiple tabs in the same spreadsheet.
     * Reads E2:E and S2:S separately to avoid row shifting from the Sheets API.
     * Returns: [clientName => totalMoneyFloat]
     */
    private function rfqTotalsFromTabs(Sheets $sheets, string $spreadsheetId, array $tabNames): array
    {
        $acc = [];

        foreach ($tabNames as $tab) {
            $clients = $sheets->spreadsheets_values->get($spreadsheetId, "'{$tab}'!E2:E")->getValues() ?? [];
            $values  = $sheets->spreadsheets_values->get($spreadsheetId, "'{$tab}'!S2:S")->getValues() ?? [];

            $maxRows = max(count($clients), count($values));
            for ($i = 0; $i < $maxRows; $i++) {
                $client = isset($clients[$i][0]) ? trim((string)$clients[$i][0]) : '';
                $valRaw = isset($values[$i][0])  ? (string)$values[$i][0] : '';

                if ($client === '') continue;

                $num = $this->parseMoney($valRaw);
                if ($num === null) continue;

                $acc[$client] = ($acc[$client] ?? 0.0) + $num;
            }
        }

        return $acc;
    }

    /** Count unique values across multiple tabs/one column. */
    private function countUniqueAcrossTabs(
        Sheets $sheets,
        string $spreadsheetId,
        array $tabs,
        string $column,
        bool $skipHeader = true,
        int $startRow = 1
    ): int {
        $set = [];
        foreach ($tabs as $tab) {
            $sr = $skipHeader ? max(2, $startRow) : $startRow;
            $range = "'{$tab}'!{$column}{$sr}:{$column}";
            $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range)->getValues() ?? [];
            foreach ($rows as $r) {
                $cell = isset($r[0]) ? trim((string)$r[0]) : '';
                if ($cell !== '') $set[$cell] = true;
            }
        }
        return count($set);
    }

    /** Sum numeric money-like values across multiple tabs/one column. */
    private function sumNumericAcrossTabs(
        Sheets $sheets,
        string $spreadsheetId,
        array $tabs,
        string $column,
        bool $skipHeader = true,
        int $startRow = 1
    ): float {
        $sum = 0.0;
        foreach ($tabs as $tab) {
            $sr = $skipHeader ? max(2, $startRow) : $startRow;
            $range = "'{$tab}'!{$column}{$sr}:{$column}";
            $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range)->getValues() ?? [];
            foreach ($rows as $r) {
                $num = $this->parseMoney(isset($r[0]) ? (string)$r[0] : '');
                if ($num !== null) $sum += $num;
            }
        }
        return $sum;
    }

    /** Build [key => sum(value)] across multiple tabs: e.g., F=client, T=amount. */
    private function totalsByKeyAcrossTabs(
        Sheets $sheets,
        string $spreadsheetId,
        array $tabs,
        string $keyCol,
        string $valCol,
        int $startRow = 2
    ): array {
        $acc = [];
        foreach ($tabs as $tab) {
            $keys   = $sheets->spreadsheets_values->get($spreadsheetId, "'{$tab}'!{$keyCol}{$startRow}:{$keyCol}")->getValues() ?? [];
            $values = $sheets->spreadsheets_values->get($spreadsheetId, "'{$tab}'!{$valCol}{$startRow}:{$valCol}")->getValues() ?? [];
            $max = max(count($keys), count($values));
            for ($i = 0; $i < $max; $i++) {
                $key = isset($keys[$i][0]) ? trim((string)$keys[$i][0]) : '';
                if ($key === '') continue;
                $num = $this->parseMoney(isset($values[$i][0]) ? (string)$values[$i][0] : '');
                if ($num === null) continue;
                $acc[$key] = ($acc[$key] ?? 0.0) + $num;
            }
        }
        return $acc;
    }

    /**
 * Count all non-empty cells across multiple tabs in one column.
 * Trims whitespace, so cells that are just "   " are NOT counted.
 */
private function countFilledAcrossTabs(
    Sheets $sheets,
    string $spreadsheetId,
    array $tabs,
    string $column,
    int $startRow = 1
): int {
    $count = 0;
    foreach ($tabs as $tab) {
        $range = "'{$tab}'!{$column}{$startRow}:{$column}";
        $rows  = $sheets->spreadsheets_values->get($spreadsheetId, $range)->getValues() ?? [];
        foreach ($rows as $r) {
            $cell = isset($r[0]) ? trim((string)$r[0]) : '';
            if ($cell !== '') {
                $count++;
            }
        }
    }
    return $count;
}
}
