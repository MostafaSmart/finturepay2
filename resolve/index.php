<?php
require_once __DIR__ . '/../data/cone.php';

// 1. إعدادات البيئة
header('Content-Type: application/json');
date_default_timezone_set('Asia/Aden'); 

// 2. دالة جلب الـ IP من خلف Docker Proxy
function getRealIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

$fingerprint = $_GET['fingerprint'] ?? null;
$device_type = strtolower($_GET['device'] ?? '');
$ip = getRealIP();

if (!$fingerprint) {
    echo json_encode(['code' => '', 'status' => 'error', 'message' => 'Fingerprint required']);
    exit;
}

try {
    // 3. البحث باستخدام نافذة زمنية دقيقة (30 دقيقة)
    // نستخدم التوقيت الحالي من PHP لضمان المطابقة مع السيرفر
    $stmt = $pdo->prepare("
        SELECT id, invite_code, user_agent 
        FROM invite_log 
        WHERE device_fingerprint IS NULL 
        AND ip = :ip 
        AND created_at >= (NOW() - INTERVAL 30 MINUTE)
        ORDER BY id DESC
    ");
    $stmt->execute([':ip' => $ip]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matched_row = null;

    // 4. مطابقة نوع الجهاز مع الـ User Agent
    foreach ($results as $row) {
        $ua = strtolower($row['user_agent']);
        if (($device_type === 'ios' && (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false)) ||
            ($device_type === 'android' && strpos($ua, 'android') !== false)) {
            $matched_row = $row;
            break;
        }
    }

    if ($matched_row) {
        // 5. ربط البصمة بالدعوة (Consume the invite)
        $update = $pdo->prepare("UPDATE invite_log SET device_fingerprint = :fp WHERE id = :id");
        $update->execute([':fp' => $fingerprint, ':id' => $matched_row['id']]);

        echo json_encode(['status' => 'success', 'code' => $matched_row['invite_code']]);
    } else {
        echo json_encode(['status' => 'no_match', 'code' => '']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database issue']);
}