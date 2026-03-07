<?php
require_once __DIR__ . '/../data/cone.php';

// قراءة الـ fingerprint من GET
$fingerprint = $_GET['fingerprint'] ?? null;

// إذا لم يوجد fingerprint
if (!$fingerprint) {
    header('Content-Type: application/json');
    echo json_encode(['code' => '']);
    exit;
}

// الحصول على IP فقط
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

// البحث عن آخر invite بدون fingerprint لهذا الجهاز (حسب IP فقط)
$stmt = $pdo->prepare("
    SELECT id, invite_code
    FROM invite_log
    WHERE device_fingerprint IS NULL AND ip = :ip
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([':ip' => $ip]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// إذا وجد invite
$code = '';
if ($row) {
    $code = $row['invite_code'];

    // تحديث السطر وربط Fingerprint
    $stmt2 = $pdo->prepare("
        UPDATE invite_log SET device_fingerprint = :fp WHERE id = :id
    ");
    $stmt2->execute([
        ':fp' => $fingerprint,
        ':id' => $row['id']
    ]);
}

// ======= تجاوز صفحة Anti-Bot إذا أرسل i=1 =======
if (isset($_GET['i'])) {
    header('Content-Type: application/json');
    echo json_encode(['code' => $code]);
    exit;
}

// باقي الكود العادي
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resolve Invite</title>
</head>
<body>
    <h1>Invite Code Resolve</h1>
    <p>Invite code for this device: <?php echo htmlspecialchars($code); ?></p>
</body>
</html>