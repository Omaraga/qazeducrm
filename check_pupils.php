<?php
// Скрипт для проверки тестовых учеников
$pdo = new PDO('mysql:host=localhost;dbname=qazeducrm', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Checking test pupils ===\n\n";

// Проверяем учеников с тестовым телефоном
$stmt = $pdo->query("SELECT id, first_name, last_name, parent_phone, phone, organization_id, status, is_deleted FROM pupil WHERE parent_phone LIKE '%7771234567%' OR phone LIKE '%7771234567%'");
$pupils = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Pupils with phone 7771234567:\n";
print_r($pupils);

// Проверяем организацию
$stmt = $pdo->query("SELECT id, name, status FROM organization WHERE is_deleted = 0 LIMIT 5");
$orgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nOrganizations:\n";
print_r($orgs);

// Проверяем все ученики организации 1
$stmt = $pdo->query("SELECT id, first_name, last_name, parent_phone, status, is_deleted FROM pupil WHERE organization_id = 1 LIMIT 10");
$allPupils = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nAll pupils in org 1 (first 10):\n";
print_r($allPupils);
