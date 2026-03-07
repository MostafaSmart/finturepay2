<?php
// بيانات الاتصال بقاعدة البيانات
$DB_HOST = "sql112.yzz.me";
$DB_USER = "yzzme_41332687";
$DB_PASS = "777296572mosmos";
$DB_NAME = "yzzme_41332687_app_link";
$DB_PROT = 3306;

// إنشاء الاتصال
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ضبط الترميز
$conn->set_charset("utf8");