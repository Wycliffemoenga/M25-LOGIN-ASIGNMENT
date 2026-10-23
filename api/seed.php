<?php
/**
 * Database Reset & Reseed Utility API
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || !has_role('admin')) {
    json_response(['success' => false, 'message' => 'Administrator privileges required.'], 403);
}

try {
    $pdo = get_db_connection();
    $schemaFile = __DIR__ . '/../database/schema.sql';
    $seedFile = __DIR__ . '/../database/seed_data.sql';

    if (!file_exists($schemaFile) || !file_exists($seedFile)) {
        json_response(['success' => false, 'message' => 'SQL files missing.'], 500);
    }

    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);

    $seedSql = file_get_contents($seedFile);
    $pdo->exec($seedSql);

    json_response([
        'success' => true,
        'message' => 'Database successfully reset and re-seeded with demo data!'
    ]);
} catch (Exception $e) {
    json_response([
        'success' => false,
        'message' => 'Reset failed: ' . $e->getMessage()
    ], 500);
}
