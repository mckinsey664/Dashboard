<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Storage;

class SheetSnapshotService
{
    /**
     * Fetch needed columns (batched) and store a local JSON snapshot.
     * Returns the payload you can use immediately.
     */
    public function refresh(): array
    {
        $client = $this->googleClient();
        $sheets = new Sheets($client);

        $masterId = env('GSHEET_MASTER_ID', '1DWxMnzTqCNaz9xTkQYcE0jDjukpD_9nnUogz5Cd8SLA');
        $maxRow   = (int) env('GSHEET_MAX_ROW', 20000);

        // ONE batched call for all columns your Blade needs (order matters)
        $ranges = [
            "'Priced Items info'!K2:K{$maxRow}", // 0: Items for quotation
            "'Priced Items info'!L2:L{$maxRow}", // 1: Quoted items
            "'Priced Items info'!I2:I{$maxRow}", // 2: Active/Passive
            "'Priced Items info'!E2:E{$maxRow}", // 3: Client
            "'Priced Items info'!S2:S{$maxRow}", // 4: Value
            "'Priced Items info'!G2:G{$maxRow}", // 5: Quantity
            "'Priced Items info'!R2:R{$maxRow}", // 6: Supplier
            "'Priced Items info'!J2:J{$maxRow}", // 7: Category
            "'Priced Items info'!H2:H{$maxRow}", // 8: Brand
        ];

        $resp = $sheets->spreadsheets_values->batchGet($masterId, [
            'ranges'            => $ranges,
            'majorDimension'    => 'ROWS',
            'valueRenderOption' => 'UNFORMATTED_VALUE',
        ]);

        $valueRanges = array_map(fn($vr) => $vr->getValues() ?? [], $resp->getValueRanges());

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'sheet_id'     => $masterId,
            'ranges'       => $ranges,
            // index map: 0:K, 1:L, 2:I, 3:E, 4:S, 5:G, 6:R, 7:J, 8:H
            'columns'      => $valueRanges,
        ];

        Storage::makeDirectory('snapshots');
        Storage::put('snapshots/sheet_stats.json', json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

        return $payload;
    }

    private function googleClient(): Client
    {
        $client = new Client();
        $client->setApplicationName('Laravel Google Sheets Snapshot');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY, 'https://www.googleapis.com/auth/drive.readonly']);
        $client->setAuthConfig(Storage::path('google/credentials.json'));
        $client->setAccessType('offline');

        $tokenPath = Storage::path('google/token.json');
        if (file_exists($tokenPath)) {
            $client->setAccessToken(json_decode(file_get_contents($tokenPath), true));
        }
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            } else {
                // Let caller handle missing OAuth
                throw new \RuntimeException('Google auth required (no refresh token).');
            }
        }
        return $client;
    }
}
