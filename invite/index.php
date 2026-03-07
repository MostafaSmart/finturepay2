<?php
require_once __DIR__ . '/../data/cone.php';

// 1. جلب كود الدعوة
$invite_code = $_GET['code'] ?? null;
if (!$invite_code) {
    echo "رابط دعوة غير صالح.";
    exit;
}

// 2. تسجيل البيانات في الخلفية (احتياطي)
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$is_ios = preg_match('/iPhone|iPad|iPod/', $user_agent);
$is_android = preg_match('/Android/', $user_agent);

try {
    $stmt = $pdo->prepare("
        INSERT INTO invite_log (invite_code, ip, user_agent, created_at)
        VALUES (:code, :ip, :ua, NOW())
    ");
    $stmt->execute([':code' => $invite_code, ':ip' => $ip, ':ua' => $user_agent]);
} catch (PDOException $e) {
    // سجل الخطأ داخلياً ولا توقف المستخدم
}

// 3. تحديد روابط المتاجر
$package = "com.ultimate.finturepay.test";
$play_url = "https://play.google.com/store/apps/details?id=$package&referrer=" . urlencode($invite_code);
$appstore_url = "https://apps.apple.com/app/idXXXXXXXXX?pt=123&ct=" . urlencode($invite_code);

$final_url = $is_android ? $play_url : ($is_ios ? $appstore_url : "#");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل Finture Pay</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; background: #f4f7f6; }
        .btn { background: #27ae60; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; margin-top: 20px; cursor: pointer; border: none; }
        .msg { color: #555; margin-top: 10px; font-size: 0.9em; }
    </style>
</head>
<body>

    <h2>مرحباً بك في Finture Pay</h2>
    <p>لقد تمت دعوتك باستخدام الكود: <strong><?php echo htmlspecialchars($invite_code); ?></strong></p>

    <button id="downloadBtn" class="btn">تحميل التطبيق وقبول الدعوة</button>
    
    <p id="status" class="msg">سيتم تحويلك للمتجر وتفعيل الكود تلقائياً</p>

    <script>
        document.getElementById('downloadBtn').addEventListener('click', function() {
            // كود النسخ للحافظة
            const code = "<?php echo $invite_code; ?>";
            const textArea = document.createElement("textarea");
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                document.getElementById('status').innerText = "تم نسخ الكود! جاري التحويل...";
            } catch (err) {
                console.error('فشل النسخ');
            }
            
            document.body.removeChild(textArea);

            // التحويل للمتجر بعد 500ms لضمان اكتمال النسخ
            setTimeout(function() {
                window.location.href = "<?php echo $final_url; ?>";
            }, 500);
        });
    </script>
</body>
</html>