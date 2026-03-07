<?php
// بيانات الاتصال بقاعدة البيانات
$DB_HOST = "sql308.infinityfree.com";
$DB_USER = "if0_41325439";
$DB_PASS = "777296572MosMos";
$DB_NAME = "if0_41325439_XXX";
$DB_PROT = 3306;

// إنشاء الاتصال
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ضبط الترميز
$conn->set_charset("utf8");