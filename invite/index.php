<?php
require_once __DIR__ . '/../data/cone.php';

$invite_code = $_GET['code'] ?? null;
if (!$invite_code) {
    echo "رابط دعوة غير صالح.";
    exit;
}

// 1. استخراج معلومات الجهاز
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$is_ios = preg_match('/iPhone|iPad|iPod/', $user_agent);
$is_android = preg_match('/Android/', $user_agent);

// 2. تسجيل العملية في قاعدة البيانات (لجميع الأجهزة)
try {
    $stmt = $pdo->prepare("INSERT INTO invite_log (invite_code, ip, user_agent, created_at) VALUES (:code, :ip, :ua, NOW())");
    $stmt->execute([':code' => $invite_code, ':ip' => $ip, ':ua' => $user_agent]);
} catch (PDOException $e) {
}

// 3. منطق التحويل (Redirect Logic)
if ($is_android) {
    // أندرويد: تحويل مباشر فوري مع التمرير للمتجر
    $package = "com.ultimate.finturepay.test";
    $play_url = "https://play.google.com/store/apps/details?id=$package&referrer=" . urlencode($invite_code);
    header("Location: $play_url");
    exit;
}

// 4. إذا كان iOS (أو أي نظام آخر): نعرض صفحة الـ Clipboard
$appstore_url = "https://apps.apple.com/app/idXXXXXXXXX?pt=123&ct=" . urlencode($invite_code);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finture Pay - قبول الدعوة</title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            padding: 40px 20px;
            background-color: #f8f9fa;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }

        .btn {
            background: #1a73e8;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
        }

        .code-display {
            font-size: 1.5em;
            color: #1a73e8;
            font-weight: bold;
            margin: 15px 0;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2>أهلاً بك في Finture Pay</h2>
        <p>لقد تمت دعوتك بواسطة الكود:</p>
        <div class="code-display"><?php echo htmlspecialchars($invite_code); ?></div>

        <button id="copyAndGo" class="btn">نسخ الكود وتحميل التطبيق</button>
        <p style="font-size: 0.8em; color: #666; margin-top: 15px;">سيتم نسخ الكود تلقائياً لتسهيل عملية التسجيل</p>
    </div>

    <script>
        document.getElementById('copyAndGo').addEventListener('click', function () {
            // جلب الرابط الكامل الحالي للصفحة
            const fullUrl = window.location.href;
            const iosUrl = "<?php echo $appstore_url; ?>";

            if (navigator.clipboard) {
                navigator.clipboard.writeText(fullUrl).then(redirect);
            } else {
                const el = document.createElement('textarea');
                el.value = fullUrl;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                redirect();
            }

            function redirect() {
                setTimeout(() => { window.location.href = iosUrl; }, 500);
            }
        });
    </script>

</body>

</html>