<?php
$host = "dpg-d6ma66paae7s73ff1ue0-a.oregon-postgres.render.com";
$dbname = "finturepay2";
$user = "finturepay2_user";
$password = "RP7s1xBnbyU4vAqhI2VECch8pr3NoDAV";
try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT NOW() AS current_time");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}