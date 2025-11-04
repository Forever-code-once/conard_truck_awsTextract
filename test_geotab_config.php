<?php
// test_geotab_config.php - PHP 7.4 compatible
// Include your existing files (adjust paths as needed)
include_once('application.php'); // Assuming this sets up DB connection via simple_query()
include_once('functions_geotab.php');
include_once('functions_geotab_usage.php');

// Function to check for DM2-like schema in your local DB
function check_for_dm2_schema() {
    $dm2_indicators = [
        // DM2 table examples from Geotab docs (e.g., LogRecords with specific fields)
        'LogRecords' => ['GeotabId', 'DeviceId', 'Latitude', 'Longitude', 'DateTime'], // For locations
        'Devices' => ['GeotabId', 'SerialNumber', 'VIN', 'Name'], // For trucks/devices
        'Trips' => ['GeotabId', 'DeviceId', 'DriverId', 'Start', 'Stop'], // For trips
        'Zones' => ['GeotabId', 'Name', 'Points', 'CentroidLatitude'] // For stops/zones
    ];

    $results = [];
    foreach ($dm2_indicators as $table => $fields) {
        // Check if table exists
        $sql = "SHOW TABLES LIKE '" . $table . "'";
        $data = simple_query($sql); // Using your simple_query() from application.php
        if (mysqli_num_rows($data) > 0) {
            // Table exists; check fields
            $field_check = [];
            foreach ($fields as $field) {
                $sql_field = "SHOW COLUMNS FROM " . $table . " LIKE '" . $field . "'";
                $data_field = simple_query($sql_field);
                $field_check[$field] = (mysqli_num_rows($data_field) > 0) ? 'Present' : 'Missing';
            }
            $results[$table] = $field_check;
        } else {
            $results[$table] = 'Table not found';
        }
    }

    // If no DM2 tables/fields found, likely not using Adapter/DM2
    $using_dm2 = false;
    foreach ($results as $status) {
        if (is_array($status) && count(array_filter($status, fn($v) => $v === 'Present')) > 0) {
            $using_dm2 = true;
            break;
        }
    }

    return ['using_dm2' => $using_dm2, 'details' => $results];
}

// Test location fetching (adapt to your feed_type=97 logic)
function test_truck_locations() {
    // Force process locations (from your mrr_process_current_geotab_location_of_trucks())
    $output = mrr_process_current_geotab_location_of_trucks(1, 0); // 1=save, 0=no view

    // If blank, debug: Try direct LogRecord feed (feed_type=1)
    if (empty($output) || strpos($output, 'No data') !== false) {
        $debug_feed = mrr_get_geotab_get_datafeed('LogRecord'); // From functions_geotab.php
        $output .= "\nDebug: Direct LogRecord feed response: " . print_r($debug_feed, true);
    }

    return $output;
}

// Run tests
$dm2_check = check_for_dm2_schema();
$locations_output = test_truck_locations();

// Output results (HTML for browser, plain for CLI)
if (php_sapi_name() === 'cli') {
    echo "DM2 Check: " . ($dm2_check['using_dm2'] ? 'Likely using DM2' : 'Not using DM2/Adapter') . "\n";
    echo "Schema Details: " . print_r($dm2_check['details'], true) . "\n";
    echo "Truck Locations Test: " . $locations_output . "\n";
} else {
    echo "<h2>Geotab Config Test</h2>";
    echo "<h3>DM2 Schema Check</h3>";
    echo "<p>" . ($dm2_check['using_dm2'] ? 'Your DB schema has DM2-like elements (possible Adapter update?).' : 'No DM2 schema detected. Not using Adapter.') . "</p>";
    echo "<pre>" . print_r($dm2_check['details'], true) . "</pre>";
    echo "<h3>Truck Locations Test (Feed Type 97 Equivalent)</h3>";
    echo "<pre>" . htmlspecialchars($locations_output) . "</pre>";
}
?>