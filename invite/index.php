<?php
require_once __DIR__ . '/../data/cone.php';

// 1. جلب كود الدعوة
$invite_code = $_GET['code'] ?? null;
if (!$invite_code) {
    echo "رابط دعوة غير صالح.";
    exit;
}

// 2. تسجيل البيانات في الخلفية (احتياطي) كما فعلت سابقاً
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$is_ios = preg_match('/iPhone|iPad|iPod/', $user_agent);
$is_android = preg_match('/Android/', $user_agent);

try {
    $stmt = $pdo->prepare("INSERT INTO invite_log (invite_code, ip, user_agent, created_at) VALUES (:code, :ip, :ua, NOW())");
    $stmt->execute([':code' => $invite_code, ':ip' => $ip, ':ua' => $user_agent]);
} catch (PDOException $e) { }

// 3. تحديد الروابط للمتصفح (بدون عمل Redirect فوري)
$package = "com.ultimate.finturepay.test";
$play_url = "https://play.google.com/store/apps/details?id=$package&referrer=" . urlencode($invite_code);
$appstore_url = "https://apps.apple.com/app/idXXXXXXXXX?pt=123&ct=" . urlencode($invite_code);

// الرابط النهائي الذي سيستخدمه JavaScript
$final_url = $is_android ? $play_url : ($is_ios ? $appstore_url : "#");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finture Pay - قبول الدعوة</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; padding: 40px 20px; background-color: #f8f9fa; color: #333; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 400px; margin: auto; }
        .logo { width: 80px; margin-bottom: 20px; }
        .invite-box { background: #e8f4fd; padding: 10px; border-radius: 8px; border: 1px dashed #2196F3; margin: 20px 0; font-weight: bold; font-size: 1.2em; color: #1565C0; }
        .btn { background: #1a73e8; color: white; padding: 15px 25px; border: none; border-radius: 8px; font-size: 1.1em; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.3s; }
        .btn:active { background: #1557b0; }
        .footer-text { margin-top: 15px; font-size: 0.85em; color: #666; }
    </style>
</head>
<body>

<div class="card">
    <h2>مرحباً بك!</h2>
    <p>لقد تمت دعوتك للانضمام إلى <strong>Finture Pay</strong></p>
    
    <div class="invite-box" id="codeText"><?php echo htmlspecialchars($invite_code); ?></div>

    <button id="actionBtn" class="btn">تحميل التطبيق الآن</button>
    
    <p class="footer-text" id="statusMsg">سيتم نسخ كود الدعوة تلقائياً عند الضغط</p>
</div>

<script>
    document.getElementById('actionBtn').addEventListener('click', function() {
        const code = "<?php echo $invite_code; ?>";
        const targetUrl = "<?php echo $final_url; ?>";
        const status = document.getElementById('statusMsg');

        // محاولة النسخ للحافظة
        if (navigator.clipboard && navigator.clipboard.writeText) {
            // الطريقة الحديثة
            navigator.clipboard.writeText(code).then(() => {
                proceedToStore(targetUrl);
            }).catch(() => {
                fallbackCopy(code, targetUrl);
            });
        } else {
            // الطريقة القديمة للمتصفحات السابقة
            fallbackCopy(code, targetUrl);
        }
    });

    function fallbackCopy(text, url) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
        } catch (err) {}
        document.body.removeChild(textArea);
        proceedToStore(url);
    }

    function proceedToStore(url) {
        document.getElementById('statusMsg').innerText = "تم نسخ الكود.. جاري فتح المتجر";
        setTimeout(() => {
            window.location.href = url;
        }, 600);
    }
</script>

</body>
</html>