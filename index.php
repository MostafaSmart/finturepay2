<?php
require_once __DIR__ . '/data/cone.php';

// جلب كل البيانات
$stmt = $pdo->query("SELECT * FROM invite_log ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// عرض بصيغة JSON
header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);