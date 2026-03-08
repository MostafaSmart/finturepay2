<?php
require_once __DIR__ . '/../data/cone.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Aden'); 

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
    // حساب وقت "منذ 30 دقيقة" داخل PHP لضمان الدقة
    $halfHourAgo = date('Y-m-d H:i:s', strtotime('-30 minutes'));

    // تعديل الاستعلام ليكون أكثر مرونة
    $stmt = $pdo->prepare("
        SELECT id, invite_code, user_agent 
        FROM invite_log 
        WHERE device_fingerprint IS NULL 
        AND ip = :ip 
        AND created_at >= :half_hour_ago
        ORDER BY id DESC
    ");
    
    $stmt->execute([
        ':ip' => $ip,
        ':half_hour_ago' => $halfHourAgo
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matched_row = null;
    foreach ($results as $row) {
        $ua = strtolower($row['user_agent']);
        // فحص مرن للـ User Agent
        $isIosMatch = ($device_type === 'ios' && (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'macintosh') !== false));
        $isAndroidMatch = ($device_type === 'android' && strpos($ua, 'android') !== false);

        if ($isIosMatch || $isAndroidMatch) {
            $matched_row = $row;
            break;
        }
    }

    if ($matched_row) {
        $update = $pdo->prepare("UPDATE invite_log SET device_fingerprint = :fp WHERE id = :id");
        $update->execute([':fp' => $fingerprint, ':id' => $matched_row['id']]);

        echo json_encode(['status' => 'success', 'code' => $matched_row['invite_code']]);
    } else {
        // أضفنا رسالة توضيحية هنا للمساعدة في التصحيح
        echo json_encode([
            'status' => 'no_match', 
            'code' => '', 
            'debug_info' => [
                'ip_searched' => $ip,
                'device_searched' => $device_type,
                'time_limit_from' => $halfHourAgo
            ]
        ]);
    }

} catch (PDOException $e) {
    // طباعة الخطأ الحقيقي لفهم المشكلة
    echo json_encode([
        'status' => 'error', 
        'message' => 'PDO Error: ' . $e->getMessage()
    ]);
}