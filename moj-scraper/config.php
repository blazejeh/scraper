<?php
$host = 'localhost';
$db   = 'scraper_portfolio';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia: " . $e->getMessage());
}

// Tabela
$sql = "CREATE TABLE IF NOT EXISTS kontakty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    telefon VARCHAR(20) UNIQUE,
    strona_url TEXT,
    data_pobrania DATETIME DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($sql);

// Funkcja formatująca numer: XXX XXX XXX
function normalizujTelefon($tel) {
    $tel = preg_replace('/[^0-9+]/', '', $tel);
    if (substr($tel, 0, 2) === '48') $tel = substr($tel, 2);
    if (substr($tel, 0, 3) === '+48') $tel = substr($tel, 3);
    if ($tel[0] === '0') $tel = substr($tel, 1);
    if (strlen($tel) < 9) return '';
    $tel = substr($tel, -9);
    return substr($tel, 0, 3) . ' ' . substr($tel, 3, 3) . ' ' . substr($tel, 6, 3);
}
?>