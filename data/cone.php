<?php
$host = "dpg-d6ma66paae7s73ff1ue0-a.oregon-postgres.render.com";
$dbname = "finturepay2";
$user = "finturepay2_user";
$password = "RP7s1xBnbyU4vAqhI2VECch8pr3NoDAV";

try {
    // إنشاء الاتصال
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ لا تطبع أي شيء هنا
    // الاتصال جاهز للاستخدام في باقي الملفات

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}