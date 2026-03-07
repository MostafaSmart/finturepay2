<?php
require_once __DIR__ . '/../data/cone.php';

// جلب كود الدعوة
$invite_code = $_GET['code'] ?? null;
if (!$invite_code) {
    echo "رابط دعوة غير صالح.";
    exit;
}

// اكتشاف نوع الجهاز
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

$is_ios = preg_match('/iPhone|iPad|iPod/', $user_agent);
$is_android = preg_match('/Android/', $user_agent);

// تسجيل الدعوة في قاعدة البيانات
$stmt = $conn->prepare("
    INSERT INTO invite_log (invite_code, device_fingerprint, ip, user_agent)
    VALUES (?, NULL, ?, ?)
");
$stmt->bind_param("sss", $invite_code, $ip, $user_agent);
$stmt->execute();
$stmt->close();

// تحويل المستخدم حسب نوع الجهاز
if ($is_android) {
    $package = "com.ultimate.finturepay.test"; 
    $play_url = "https://play.google.com/store/apps/details?id=$package&referrer=" . urlencode($invite_code);
    header("Location: $play_url");
    exit;
}

if ($is_ios) {
    $appstore_url = "https://apps.apple.com/app/idXXXXXXXXX?pt=123&ct=" . urlencode($invite_code) . "&mt=8";
    header("Location: $appstore_url");
    exit;
}

// إذا كان Desktop أو غير معروف
echo "كود الدعوة: $invite_code. الرجاء تثبيت التطبيق لقبول الدعوة.";