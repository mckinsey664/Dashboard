<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;


class GoogleSheetController extends Controller
{

    public function index()
    {

        dd('START 1');

        set_time_limit(300); // allow up to 5 minutes
        $client = new Client();
        $client->setApplicationName('Laravel Google Sheets');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(
            json_decode(env('GOOGLE_CREDENTIALS_JSON'), true)
        );



        $service = new Sheets($client);

        // Replace with your sheet ID & range
        $spreadsheetId = '14SRB6tiCcr-UddLCGO3KLWuWNwqHGo3jQWLsg58Nzhw';

        // Define all tabs you want to merge
        $ranges = [
            'Test!A1:AL5000',
            'TestCards!A1:AL5000',
            // add more here: 'AnotherTab!A1:AL5000',
        ];

        $values = [];

        foreach ($ranges as $range) {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $tabValues = $response->getValues();

            if (!empty($tabValues)) {
                // Skip header row for all except the first tab
                if (!empty($values)) {
                    array_shift($tabValues);
                }
                $values = array_merge($values, $tabValues);
            }
        }

        return view('google_sheets', compact('values'));
    }

    public function secondSheet()
    {
        $client = $this->getClient();

        $service = new Sheets($client);
        $spreadsheetId = '1DWxMnzTqCNaz9xTkQYcE0jDjukpD_9nnUogz5Cd8SLA';
        $range = 'Priced Items info!A1:AH';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        return view('google_sheets_second', compact('values'));
    }

    private function getClient()
    {
        $client = new Client();
        $client->setApplicationName('RFQ-PO Dashboard');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(
            json_decode(env('GOOGLE_CREDENTIALS_JSON'), true)
        );
        return $client;
    }

    // public function sheetStats()
    // {
    //     $client = $this->getClient();
    //     $service = new \Google\Service\Sheets($client);

    //     $spreadsheetId = '1DWxMnzTqCNaz9xTkQYcE0jDjukpD_9nnUogz5Cd8SLA';

    //     // 🔴 USE EXACT TAB NAME (copy-paste from Google Sheets)
    //     $range = 'Priced Items info!A1:A5';

    //     $response = $service->spreadsheets_values->get($spreadsheetId, $range);

    //     return response()->json([
    //         'ok' => true,
    //         'data' => $response->getValues()
    //     ]);
    // }

    private function safeGet(\Google\Service\Sheets $service, string $sheetId, string $range): array
    {
        try {
            $response = $service->spreadsheets_values->get($sheetId, $range);
            return $response->getValues() ?? [];
        } catch (\Throwable $e) {
            logger()->error('Google Sheets error', [
                'sheet_id' => $sheetId,
                'range' => $range,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function sheetStats()
    {
        set_time_limit(300);

        try {

            // ---------- CLIENT ----------
            $client = new \Google\Client();
            $client->setApplicationName('RFQ-PO Dashboard');
            $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(
                json_decode(env('GOOGLE_CREDENTIALS_JSON'), true)
            );

            $service = new \Google\Service\Sheets($client);

            // ---------- CACHE ----------
            $stats = \Cache::remember('sheet_stats', now()->addMinutes(10), function () use ($service) {

            $SHEET_ID = '1Ibj2JAIB6xfg--RQ9BgviBZL8p1DFtSij97ILDThIko';
            $TAB = "'Priced Items info'!";

            // ===========================
            // 1️⃣ BASIC COUNTS
            // ===========================
            $colA = $this->safeGet($service, $SHEET_ID, $TAB.'A2:A');
            $colL = $this->safeGet($service, $SHEET_ID, $TAB.'L2:L');

            

            $results = [
                [
                    'name' => 'ITEMS FOR QUOTATION',
                    'filled_count' => count(array_filter($colA)),
                ],
                [
                    'name' => 'QUOTED ITEMS',
                    'filled_count' => count(array_filter($colL)),
                ],
            ];

            // ===========================
            // 2️⃣ ACTIVE vs PASSIVE
            // ===========================
            $types = $this->safeGet($service, $SHEET_ID, $TAB.'I2:I');

            $activeCount = 0;
            $passiveCount = 0;

            foreach ($types as $row) {
                $v = strtolower(trim($row[0] ?? ''));
                if ($v === 'active') $activeCount++;
                if ($v === 'passive') $passiveCount++;
            }

            // ===========================
            // 3️⃣ TOP CLIENTS BY VALUE
            // ===========================
            $clients = $this->safeGet($service, $SHEET_ID, $TAB.'E2:E');
            $values  = $this->safeGet($service, $SHEET_ID, $TAB.'S2:S');

            $totals = [];

            for ($i = 0; $i < count($clients); $i++) {
                $c = trim($clients[$i][0] ?? '');
                $v = floatval($values[$i][0] ?? 0);
                if ($c !== '') {
                    $totals[$c] = ($totals[$c] ?? 0) + $v;
                }
            }

            arsort($totals);

            $topClientsData = [];
            foreach (array_slice($totals, 0, 20, true) as $name => $total) {
                $topClientsData[] = compact('name', 'total');
            }

            $topClientName  = $topClientsData[0]['name']  ?? '';
            $topClientValue = $topClientsData[0]['total'] ?? 0;

            $lastIndex = count($topClientsData) - 1;
            $lastClientName  = $topClientsData[$lastIndex]['name']  ?? '';
            $lastClientValue = $topClientsData[$lastIndex]['total'] ?? 0;
// ===========================
// SAFE DEFAULTS (PREVENT BLADE CRASHES)
// ===========================
$topActiveClientsData = [];
$topActiveClientName = '';
$topActiveClientPercent = 0;
$lastActiveClientName = '';
$lastActiveClientPercent = 0;

$topPassiveClientsData = [];
$topPassiveClientName = '';
$topPassiveClientPercent = 0;
$lastPassiveClientName = '';
$lastPassiveClientPercent = 0;

$topActiveSuppliersData = [];
$topActiveSupplierName = '';
$topActiveSupplierPercent = 0;
$lastActiveSupplierName = '';
$lastActiveSupplierPercent = 0;

$topPassiveSuppliersData = [];
$topPassiveSupplierName = '';
$topPassiveSupplierPercent = 0;
$lastPassiveSupplierName = '';
$lastPassiveSupplierPercent = 0;

$supplierPreferences = [];
$topClientsPerCategory = [];
$topClientsPerBrand = [];


            // ===========================
            // RETURN FINAL STRUCTURE
            // ===========================
return [
    'results' => $results,
    'activeCount' => $activeCount,
    'passiveCount' => $passiveCount,

    'topClientsData' => $topClientsData,
    'topClientName' => $topClientName,
    'topClientValue' => $topClientValue,
    'lastClientName' => $lastClientName,
    'lastClientValue' => $lastClientValue,

    'topActiveClientsData' => $topActiveClientsData,
    'topActiveClientName' => $topActiveClientName,
    'topActiveClientPercent' => $topActiveClientPercent,
    'lastActiveClientName' => $lastActiveClientName,
    'lastActiveClientPercent' => $lastActiveClientPercent,

    'topPassiveClientsData' => $topPassiveClientsData,
    'topPassiveClientName' => $topPassiveClientName,
    'topPassiveClientPercent' => $topPassiveClientPercent,
    'lastPassiveClientName' => $lastPassiveClientName,
    'lastPassiveClientPercent' => $lastPassiveClientPercent,

    'topActiveSuppliersData' => $topActiveSuppliersData,
    'topActiveSupplierName' => $topActiveSupplierName,
    'topActiveSupplierPercent' => $topActiveSupplierPercent,
    'lastActiveSupplierName' => $lastActiveSupplierName,
    'lastActiveSupplierPercent' => $lastActiveSupplierPercent,

    'topPassiveSuppliersData' => $topPassiveSuppliersData,
    'topPassiveSupplierName' => $topPassiveSupplierName,
    'topPassiveSupplierPercent' => $topPassiveSupplierPercent,
    'lastPassiveSupplierName' => $lastPassiveSupplierName,
    'lastPassiveSupplierPercent' => $lastPassiveSupplierPercent,

    'supplierPreferences' => $supplierPreferences,
    'topClientsPerCategory' => $topClientsPerCategory,
    'topClientsPerBrand' => $topClientsPerBrand,
];

            });

            return view('sheet_stats', $stats);

        } catch (\Throwable $e) {

        logger()->critical('sheetStats fatal error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        abort(500, 'Dashboard temporarily unavailable');
        }
    }




    





//     public function sheetStats()
//     {
//         set_time_limit(300); // allow up to 5 minutes
//         $client = $this->getClient();

//         $service = new Sheets($client);
//         Cache::forget('sheet_stats');
        

//         // Cache for 10 minutes (adjust as needed)
//         $stats = Cache::remember('sheet_stats', now()->addMinutes(10), function () use ($service) {
        


//         $sheets = [
//             ['id' => '1DWxMnzTqCNaz9xTkQYcE0jDjukpD_9nnUogz5Cd8SLA', 'name' => 'ITEMS FOR QUOTATION', 'column' => 'A'],
//             ['id' => '1DWxMnzTqCNaz9xTkQYcE0jDjukpD_9nnUogz5Cd8SLA', 'name' => 'QUOTED ITEMS', 'column' => 'L'],
//         ];

//         $results = [];

//         foreach ($sheets as $sheet) {
//         // Always specify the exact tab name
//         $range = "'Priced Items info'!" . $sheet['column'] . '2:' . $sheet['column'];

//         $response = $service->spreadsheets_values->get($sheet['id'], $range);
//         $values = $response->getValues();

//         $filledCount = 0;
//         $filteredValues = [];

//         if (!empty($values)) {
//             foreach ($values as $row) {
//                 $cellValue = trim($row[0] ?? '');
//                 if ($cellValue !== '' && strtoupper($cellValue) !== 'NA' && strtoupper($cellValue) !== 'N/A') {
//                     $filledCount++;
//                     $filteredValues[] = $cellValue;
//                 }
//             }
//         }

//         $results[] = [
//             'name' => $sheet['name'],
//             'id' => $sheet['id'],
//             'column' => $sheet['column'],
//             'filled_count' => $filledCount,
//             'values' => $filteredValues
//         ];
//         }
    
//         // Second: Count Active vs Passive in Column I
//         $activePassiveRange = "'Priced Items info'!I2:I";
//         $apResponse = $service->spreadsheets_values->get($sheets[0]['id'], $activePassiveRange);
//         $apValues = $apResponse->getValues();

//         $activeCount = 0;
//         $passiveCount = 0;

//         if (!empty($apValues)) {
//             foreach ($apValues as $row) {
//             $cellValue = strtolower(trim($row[0] ?? ''));
//             if ($cellValue === 'active') {
//                 $activeCount++;
//             } elseif ($cellValue === 'passive') {
//                 $passiveCount++;
//             }
//             }
//         }

//         //THIRD:
//         // --- Top 20 Clients by Money Value ---
//         $clientRange = "'Priced Items info'!E2:E"; // Client Name
//         $valueRange  = "'Priced Items info'!S2:S"; // Value column (adjust if needed)

//         $clientResponse = $service->spreadsheets_values->get(
//             $sheets[0]['id'], 
//             $clientRange
//         );
//         $valueResponse  = $service->spreadsheets_values->get(
//             $sheets[0]['id'], 
//             $valueRange
//         );

//         $clientValues = $clientResponse->getValues();
//         $moneyValues  = $valueResponse->getValues();

//         $clientTotals = [];

//         for ($i = 0; $i < count($clientValues); $i++) {
//             $clientName = trim($clientValues[$i][0] ?? '');
//             $amount = floatval($moneyValues[$i][0] ?? 0); // directly cast to float

//             if ($clientName !== '') {
//                 if (!isset($clientTotals[$clientName])) {
//                     $clientTotals[$clientName] = 0;
//                 }
//                 $clientTotals[$clientName] += $amount;
//             }
//         }



//         arsort($clientTotals); // sort descending by value
//         $topClients = array_slice($clientTotals, 0, 20, true);

//         $topClientsData = [];
//         foreach ($topClients as $name => $total) {
//             $topClientsData[] = [
//                 'name' => $name,
//                 'total' => $total
//             ];
//         }

//         $topClientName = $topClientsData[0]['name'] ?? '';
//         $topClientValue = $topClientsData[0]['total'] ?? 0;
//         $lastClientName = $topClientsData[count($topClientsData)-1]['name'] ?? '';
//         $lastClientValue = $topClientsData[count($topClientsData)-1]['total'] ?? 0;

//         //forth and fifth
//         // --- Top Clients by Requested Active Components (by Quantity + Line Numbers) ---
//         // ===== ACTIVE & PASSIVE CLIENT QUANTITIES =====
//         $clientColumnRange   = "'Priced Items info'!E2:E"; // Client Name
//         $typeColumnRange     = "'Priced Items info'!I2:I"; // Component Type ("Active" / "Passive")
//         $quantityColumnRange = "'Priced Items info'!G2:G"; // Quantity

//         $clientColResponse   = $service->spreadsheets_values->get($sheets[0]['id'], $clientColumnRange);
//         $typeColResponse     = $service->spreadsheets_values->get($sheets[0]['id'], $typeColumnRange);
//         $quantityColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $quantityColumnRange);

//         $clientColValues   = $clientColResponse->getValues();
//         $typeColValues     = $typeColResponse->getValues();
//         $quantityColValues = $quantityColResponse->getValues();

//         $activeClientData = [];
//         $passiveClientData = [];

//         for ($i = 0; $i < count($clientColValues); $i++) {
//             $clientName = trim($clientColValues[$i][0] ?? '');
//             $componentType = strtolower(trim($typeColValues[$i][0] ?? ''));
//             $quantity = floatval($quantityColValues[$i][0] ?? 0);
//             $rowNumber = $i + 2; // Google Sheets row number

//             if ($clientName !== '' && $componentType === 'active') {
//                 if (!isset($activeClientData[$clientName])) {
//                     $activeClientData[$clientName] = ['total_qty' => 0, 'rows' => []];
//                 }
//                 $activeClientData[$clientName]['total_qty'] += $quantity;
//                 $activeClientData[$clientName]['rows'][] = $rowNumber;
//             }

//             if ($clientName !== '' && $componentType === 'passive') {
//                 if (!isset($passiveClientData[$clientName])) {
//                     $passiveClientData[$clientName] = ['total_qty' => 0, 'rows' => []];
//                 }
//                 $passiveClientData[$clientName]['total_qty'] += $quantity;
//                 $passiveClientData[$clientName]['rows'][] = $rowNumber;
//             }
//         }

//         // Sort both lists
//         uasort($activeClientData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);
//         uasort($passiveClientData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

//         // Take top 20
//         $topActiveClients = array_slice($activeClientData, 0, 20, true);
//         $topPassiveClients = array_slice($passiveClientData, 0, 20, true);

//         // Totals for percentages
//         $totalActiveQty = array_sum(array_column($activeClientData, 'total_qty'));
//         $totalPassiveQty = array_sum(array_column($passiveClientData, 'total_qty'));

//         // Prepare arrays for Blade
//         $topActiveClientsData = [];
//         foreach ($topActiveClients as $name => $data) {
//             $topActiveClientsData[] = [
//                 'name'       => $name,
//                 'count'      => $data['total_qty'],
//                 'percentage' => $totalActiveQty > 0 ? ($data['total_qty'] / $totalActiveQty) * 100 : 0
//             ];
//         }

//         $topPassiveClientsData = [];
//         foreach ($topPassiveClients as $name => $data) {
//         $topPassiveClientsData[] = [
//         'name'       => $name,
//         'count'      => $data['total_qty'],
//         'percentage' => $totalPassiveQty > 0 ? ($data['total_qty'] / $totalPassiveQty) * 100 : 0
//         ];
//         }

//         // For description text
//         $topActiveClientName = $topActiveClientsData[0]['name'] ?? '';
//         $topActiveClientPercent = $topActiveClientsData[0]['percentage'] ?? 0;
//         $lastActiveClientName = $topActiveClientsData[count($topActiveClientsData)-1]['name'] ?? '';
//         $lastActiveClientPercent = $topActiveClientsData[count($topActiveClientsData)-1]['percentage'] ?? 0;

//         $topPassiveClientName = $topPassiveClientsData[0]['name'] ?? '';
//         $topPassiveClientPercent = $topPassiveClientsData[0]['percentage'] ?? 0;
//         $lastPassiveClientName = $topPassiveClientsData[count($topPassiveClientsData)-1]['name'] ?? '';
//         $lastPassiveClientPercent = $topPassiveClientsData[count($topPassiveClientsData)-1]['percentage'] ?? 0;

//         //seven
//         // ===== BEST QUOTED ACTIVE COMPONENTS BY SUPPLIER =====
//         // Supplier Name column (R), Component Type column (I), Quantity column (G)
//         $supplierColumnRange = "'Priced Items info'!R2:R"; 
//         $typeColumnRange     = "'Priced Items info'!I2:I"; 
//         $quantityColumnRange = "'Priced Items info'!G2:G"; 

//         $supplierColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $supplierColumnRange);
//         $typeColResponse     = $service->spreadsheets_values->get($sheets[0]['id'], $typeColumnRange);
//         $quantityColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $quantityColumnRange);

//         $supplierColValues   = $supplierColResponse->getValues();
//         $typeColValues       = $typeColResponse->getValues();
//         $quantityColValues   = $quantityColResponse->getValues();

//         $activeSupplierData = [];

//         for ($i = 0; $i < count($supplierColValues); $i++) {
//         $supplierName   = trim($supplierColValues[$i][0] ?? '');
//         $componentType  = strtolower(trim($typeColValues[$i][0] ?? ''));
//         $quantity       = floatval($quantityColValues[$i][0] ?? 0);
//         $rowNumber      = $i + 2; // Google Sheets row number

//         if ($supplierName !== '' && $componentType === 'active') {
//         if (!isset($activeSupplierData[$supplierName])) {
//             $activeSupplierData[$supplierName] = ['total_qty' => 0, 'rows' => []];
//         }
//         $activeSupplierData[$supplierName]['total_qty'] += $quantity;
//         $activeSupplierData[$supplierName]['rows'][] = $rowNumber;
//         }
//         }

//         // Sort descending by quantity
//         uasort($activeSupplierData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

//         // Take top 20 suppliers
//         $topActiveSuppliers = array_slice($activeSupplierData, 0, 20, true);

//         // Calculate total for percentage
//         $totalActiveSupplierQty = array_sum(array_column($activeSupplierData, 'total_qty'));

//         // Prepare array for Blade
//         $topActiveSuppliersData = [];
//         foreach ($topActiveSuppliers as $name => $data) {
//             $topActiveSuppliersData[] = [
//             'name'       => $name,
//                 'count'      => $data['total_qty'],
//                 'percentage' => $totalActiveSupplierQty > 0 ? ($data['total_qty'] / $totalActiveSupplierQty) * 100 : 0
//             ];
//         }
//         // For description
//         $topActiveSupplierName = $topActiveSuppliersData[0]['name'] ?? '';
//         $topActiveSupplierPercent = $topActiveSuppliersData[0]['percentage'] ?? 0;
//         $lastActiveSupplierName = $topActiveSuppliersData[count($topActiveSuppliersData)-1]['name'] ?? '';
//         $lastActiveSupplierPercent = $topActiveSuppliersData[count($topActiveSuppliersData)-1]['percentage'] ?? 0;

//         // ===== BEST QUOTED PASSIVE COMPONENTS BY SUPPLIER =====
//         // Supplier Name column (R), Component Type column (I), Quantity column (G)
//         $supplierColumnRange = "'Priced Items info'!R2:R"; 
//         $typeColumnRange     = "'Priced Items info'!I2:I"; 
//         $quantityColumnRange = "'Priced Items info'!G2:G"; 

//         $supplierColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $supplierColumnRange);
//         $typeColResponse     = $service->spreadsheets_values->get($sheets[0]['id'], $typeColumnRange);
//         $quantityColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $quantityColumnRange);

//         $supplierColValues   = $supplierColResponse->getValues();
//         $typeColValues       = $typeColResponse->getValues();
//         $quantityColValues   = $quantityColResponse->getValues();
//         $passiveSupplierData = [];

//         for ($i = 0; $i < count($supplierColValues); $i++) {
//             $supplierName   = trim($supplierColValues[$i][0] ?? '');
//             $componentType  = strtolower(trim($typeColValues[$i][0] ?? ''));
//             $quantity       = floatval($quantityColValues[$i][0] ?? 0);
//             $rowNumber      = $i + 2; // Google Sheets row number

//             if ($supplierName !== '' && $componentType === 'passive') {
//                 if (!isset($passiveSupplierData[$supplierName])) {
//                     $passiveSupplierData[$supplierName] = ['total_qty' => 0, 'rows' => []];
//                 }
//                 $passiveSupplierData[$supplierName]['total_qty'] += $quantity;
//                 $passiveSupplierData[$supplierName]['rows'][] = $rowNumber;
//             }
//         }

//         // Sort descending by quantity
//         uasort($passiveSupplierData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

//         // Take top 20 suppliers
//         $topPassiveSuppliers = array_slice($passiveSupplierData, 0, 20, true);

//         // Calculate total for percentage
//         $totalPassiveSupplierQty = array_sum(array_column($passiveSupplierData, 'total_qty'));

//         // Prepare array for Blade
//         $topPassiveSuppliersData = [];
//         foreach ($topPassiveSuppliers as $name => $data) {
//             $topPassiveSuppliersData[] = [
//                 'name'       => $name,
//                 'count'      => $data['total_qty'],
//                 'percentage' => $totalPassiveSupplierQty > 0 ? ($data['total_qty'] / $totalPassiveSupplierQty) * 100 : 0,
//                 'rows'       => $data['rows']
//             ];
//         }

//         // For description
//         $topPassiveSupplierName = $topPassiveSuppliersData[0]['name'] ?? '';
//         $topPassiveSupplierPercent = $topPassiveSuppliersData[0]['percentage'] ?? 0;
//         $lastPassiveSupplierName = $topPassiveSuppliersData[count($topPassiveSuppliersData)-1]['name'] ?? '';
//         $lastPassiveSupplierPercent = $topPassiveSuppliersData[count($topPassiveSuppliersData)-1]['percentage'] ?? 0;   
//         // ===== SUPPLIERS’ QUOTING PREFERENCES BY CATEGORY =====
//         // Supplier Name column (R), Category column (J)
//         $supplierColumnRange = "'Priced Items info'!R2:R"; 
//         $categoryColumnRange = "'Priced Items info'!J2:J"; 

//         $supplierColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $supplierColumnRange);
//         $categoryColResponse = $service->spreadsheets_values->get($sheets[0]['id'], $categoryColumnRange);
//         $supplierColValues = $supplierColResponse->getValues();
//         $categoryColValues = $categoryColResponse->getValues();

//         $supplierCategoryCounts = [];

//         for ($i = 0; $i < count($supplierColValues); $i++) {
//             $supplierName = trim($supplierColValues[$i][0] ?? '');
//             //$categoryName = trim($categoryColValues[$i][0] ?? '');
//             $categoryName = strtolower(trim($categoryColValues[$i][0] ?? ''));


//             if ($supplierName !== '' && $categoryName !== '' && $categoryName !== 'not found' &&
//                 $categoryName !== 'na' && $categoryName !== 'n/a') {
//                 if (!isset($supplierCategoryCounts[$supplierName])) {
//                     $supplierCategoryCounts[$supplierName] = [];
//                 }
//                 if (!isset($supplierCategoryCounts[$supplierName][$categoryName])) {
//                     $supplierCategoryCounts[$supplierName][$categoryName] = 0;
//                 }
//                 $supplierCategoryCounts[$supplierName][$categoryName]++;
//             }
//         }

// // Sort each supplier's categories by count & take top 3
// $supplierPreferences = [];
// foreach ($supplierCategoryCounts as $supplier => $categories) {
//     arsort($categories);
// $topCategories = array_slice($categories, 0, 3, true);

// $supplierPreferences[] = [
//     'supplier' => $supplier,
//     'best'     => array_key_first($topCategories) ?? '',
//     'best_count' => reset($topCategories) ?: 0,
//     'second'   => array_keys($topCategories)[1] ?? '',
//     'second_count' => array_values($topCategories)[1] ?? 0,
//     'third'    => array_keys($topCategories)[2] ?? '',
//     'third_count' => array_values($topCategories)[2] ?? 0,
// ];

// }

// // Sort suppliers alphabetically
// usort($supplierPreferences, fn($a, $b) => strcmp($a['supplier'], $b['supplier']));


// // ===== MOST REQUESTED CATEGORIES (Top 3 Clients per Category) =====
// // Category column (J), Client column (E)
// $categoryColumnRange = "'Priced Items info'!J2:J";
// $clientColumnRange = "'Priced Items info'!E2:E";

// $categoryResponse = $service->spreadsheets_values->get($sheets[0]['id'], $categoryColumnRange);
// $clientResponse = $service->spreadsheets_values->get($sheets[0]['id'], $clientColumnRange);

// $categoryValues = $categoryResponse->getValues();
// $clientValues = $clientResponse->getValues();

// $categoryClientCounts = [];

// for ($i = 0; $i < count($categoryValues); $i++) {
//     $category = trim($categoryValues[$i][0] ?? '');
//     $client = trim($clientValues[$i][0] ?? '');

//     if ($category !== '' && $client !== '') {
//         if (!isset($categoryClientCounts[$category])) {
//             $categoryClientCounts[$category] = [];
//         }
//         if (!isset($categoryClientCounts[$category][$client])) {
//             $categoryClientCounts[$category][$client] = 0;
//         }
//         $categoryClientCounts[$category][$client]++;
//     }
// }

// // Prepare top 3 clients per category
// // Sort categories by total requests
// $categoryTotals = [];
// foreach ($categoryClientCounts as $category => $clients) {
//     $categoryTotals[$category] = array_sum($clients);
// }
// arsort($categoryTotals);

// // Take top 20 categories by total requests
// $top20Categories = array_slice($categoryTotals, 0, 20, true);

// $topClientsPerCategory = [];
// foreach ($top20Categories as $category => $total) {
//     $clients = $categoryClientCounts[$category];
//     arsort($clients);
//     $topClients = array_slice($clients, 0, 3, true);
//     $clientNames = array_keys($topClients);

//     $topClientsPerCategory[] = [
//         'category' => $category,
//         'top1' => $clientNames[0] ?? '',
//         'top2' => $clientNames[1] ?? '',
//         'top3' => $clientNames[2] ?? '',
//     ];
// }

// // ===== MOST REQUESTED BRANDS (Top 3 Clients per Brand) =====
// // Brand = column H, Client = column E
// $brandColumnRange = "'Priced Items info'!H2:H"; // Manufacturer
// $clientColumnRange = "'Priced Items info'!E2:E"; // Client

// $brandResponse = $service->spreadsheets_values->get($sheets[0]['id'], $brandColumnRange);
// $clientResponse = $service->spreadsheets_values->get($sheets[0]['id'], $clientColumnRange);

// $brandValues = $brandResponse->getValues();
// $clientValues = $clientResponse->getValues();

// $brandClientCounts = [];

// for ($i = 0; $i < count($brandValues); $i++) {
//     $brand = trim($brandValues[$i][0] ?? '');
//     $client = trim($clientValues[$i][0] ?? '');

//     if ($brand !== '' && $client !== '') {
//         if (!isset($brandClientCounts[$brand])) {
//             $brandClientCounts[$brand] = [];
//         }
//         if (!isset($brandClientCounts[$brand][$client])) {
//             $brandClientCounts[$brand][$client] = 0;
//         }
//         $brandClientCounts[$brand][$client]++;
//     }
// }

// // Sort brands by total requests
// $brandTotals = [];
// foreach ($brandClientCounts as $brand => $clients) {
//     $brandTotals[$brand] = array_sum($clients);
// }
// arsort($brandTotals);

// // Take top 20 brands
// $top20Brands = array_slice($brandTotals, 0, 20, true);

// $topClientsPerBrand = [];
// foreach ($top20Brands as $brand => $total) {
//     $clients = $brandClientCounts[$brand];
//     arsort($clients);
//     $topClients = array_slice($clients, 0, 3, true);
//     $clientNames = array_keys($topClients);

//     $topClientsPerBrand[] = [
//         'brand' => $brand,
//         'top1' => $clientNames[0] ?? '',
//         'top2' => $clientNames[1] ?? '',
//         'top3' => $clientNames[2] ?? '',
//     ];
// }


// return [
//             'results' => $results,
//             'activeCount' => $activeCount,
//             'passiveCount' => $passiveCount,
//             'topClientsData' => $topClientsData,
//             'topClientName' => $topClientName,
//             'topClientValue' => $topClientValue,
//             'lastClientName' => $lastClientName,
//             'lastClientValue' => $lastClientValue,
//             'topActiveClientsData' => $topActiveClientsData,
//             'topActiveClientName' => $topActiveClientName,
//             'topActiveClientPercent' => $topActiveClientPercent,
//             'lastActiveClientName' => $lastActiveClientName,
//             'lastActiveClientPercent' => $lastActiveClientPercent,
//             'topPassiveClientsData' => $topPassiveClientsData,
//             'topPassiveClientName' => $topPassiveClientName,
//             'topPassiveClientPercent' => $topPassiveClientPercent,
//             'lastPassiveClientName' => $lastPassiveClientName,
//             'lastPassiveClientPercent' => $lastPassiveClientPercent,
//             'topActiveSuppliersData' => $topActiveSuppliersData,
//             'topActiveSupplierName' => $topActiveSupplierName,
//             'topActiveSupplierPercent' => $topActiveSupplierPercent,
//             'lastActiveSupplierName' => $lastActiveSupplierName,
//             'lastActiveSupplierPercent' => $lastActiveSupplierPercent,
//             'topPassiveSuppliersData' => $topPassiveSuppliersData,
//             'topPassiveSupplierName' => $topPassiveSupplierName,
//             'topPassiveSupplierPercent' => $topPassiveSupplierPercent,
//             'lastPassiveSupplierName' => $lastPassiveSupplierName,
//             'lastPassiveSupplierPercent' => $lastPassiveSupplierPercent,
//             'supplierPreferences' => $supplierPreferences,
//             'topClientsPerCategory' => $topClientsPerCategory,
//             'topClientsPerBrand' => $topClientsPerBrand,
//         ];
//         // --------------------------------------------------
//         // ⬆️ End of logic block
//     });

//     // ✅ Return the cached (or freshly computed) result to the view
//     return view('sheet_stats', $stats);
// }






}