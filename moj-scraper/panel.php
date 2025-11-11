<?php
require 'config.php';

if (isset($_GET['wyczysc'])) {
    $pdo->exec("DELETE FROM kontakty");
    $pdo->exec("ALTER TABLE kontakty AUTO_INCREMENT = 1");
    header("Location: panel.php");
    exit;
}

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="kontakty.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Email', 'Telefon', 'Źródło', 'Data'], ';');
    $stmt = $pdo->query("SELECT * FROM kontakty ORDER BY id DESC");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['email'],
            $row['telefon'],
            $row['strona_url'],
            $row['data_pobrania']
        ], ';');
    }
    exit;
}

$stmt = $pdo->query("SELECT * FROM kontakty ORDER BY id DESC");
$wyniki = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zebrane kontakty</title>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f4f7fa; 
            color: #333; 
            position: relative;
            min-height: 100vh;
        }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 10px; font-size: 28px; }
        
        .actions-top { 
            text-align: center; 
            margin: 20px 0; 
            display: flex; 
            gap: 10px; 
            justify-content: center; 
            flex-wrap: wrap; 
        }
        
        .actions-bottom { 
            position: fixed; 
            bottom: 20px; 
            left: 50%; 
            transform: translateX(-50%);
            background: white;
            padding: 12px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .actions-bottom a {
            color: #3498db;
            font-weight: bold;
            text-decoration: none;
            font-size: 15px;
        }
        .actions-bottom a:hover { text-decoration: underline; }

        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            font-size: 16px; 
            font-weight: bold; 
            text-decoration: none; 
            color: white; 
            border-radius: 6px; 
            min-width: 140px; 
            text-align: center; 
        }
        .btn-green { background: #27ae60; }
        .btn-red { background: #dc3545; }
        .btn-blue { background: #3498db; }
        .btn-gray { background: #95a5a6; }
        .btn:hover { opacity: 0.9; }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #3498db; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .empty { text-align: center; color: #7f8c8d; font-style: italic; padding: 40px; }
    </style>
</head>
<body>

    <h1>Zebrane kontakty (<?= count($wyniki) ?>)</h1>

    <div class="actions-top">
        <a href="index.php" class="btn btn-gray">Powrót</a>
        <a href="?export=1" class="btn btn-green">Pobierz CSV</a>
        <?php if (count($wyniki) > 0): ?>
            <a href="?wyczysc=1" class="btn btn-red" 
               onclick="return confirm('Na pewno chcesz USUNĄĆ WSZYSTKIE dane i zresetować ID?');">
               Wyczyść wyniki
            </a>
        <?php endif; ?>
        <a href="scraper.php" class="btn btn-blue">Dodaj nowy scraping</a>
    </div>

    <?php if (count($wyniki) == 0): ?>
        <p class="empty">Brak danych. <a href="scraper.php">Rozpocznij scraping</a></p>
    <?php else: ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>Źródło</th>
            <th>Data</th>
        </tr>
        <?php foreach ($wyniki as $w): ?>
        <tr>
            <td><strong><?= $w['id'] ?></strong></td>
            <td><?= htmlspecialchars($w['email']) ?></td>
            <td><?= htmlspecialchars($w['telefon']) ?></td>
            <td><a href="<?= $w['strona_url'] ?>" target="_blank" style="color:#3498db;">
                <?= htmlspecialchars($w['strona_url']) ?>
            </a></td>
            <td><?= $w['data_pobrania'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <div class="actions-bottom">
        <a href="index.php">Powrót do strony głównej</a>
        <?php if (count($wyniki) > 0): ?>
            <span style="color:#95a5a6; font-size:13px;">•</span>
            <a href="?export=1" style="color:#27ae60;">Pobierz CSV</a>
        <?php endif; ?>
    </div>

</body>
</html>