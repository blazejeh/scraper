<?php
require 'config.php';
session_start();

if (!isset($_SESSION['urls'])) {
    $_SESSION['urls'] = [];
}
$urls = &$_SESSION['urls'];

function pobierzStrone($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $html = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    return $error ? false : $html;
}

// DODAJ URL
if (isset($_POST['add_url']) && !empty(trim($_POST['url']))) {
    $url = trim($_POST['url']);
    if (filter_var($url, FILTER_VALIDATE_URL) && !in_array($url, $urls)) {
        $urls[] = $url;
        $dodano = true;
    } else {
        $blad = "Nieprawidłowy lub duplikat URL!";
    }
}

// USUŃ POJEDYNCZY
if (isset($_GET['usun'])) {
    $index = (int)$_GET['usun'];
    if (isset($urls[$index])) {
        unset($urls[$index]);
        $urls = array_values($urls);
    }
    header("Location: scraper.php");
    exit;
}

// USUŃ WSZYSTKIE
if (isset($_POST['usun_wszystkie'])) {
    $_SESSION['urls'] = [];
    $urls = [];
    $usunieto_wszystkie = true;
}

// SCRAPUJ
$wyniki = [];
if (isset($_POST['scrape']) && !empty($urls)) {
    foreach ($urls as $url) {
        $html = pobierzStrone($url);
        if (!$html) {
            $wyniki[$url] = "<span style='color:red;'>Błąd pobierania</span>";
            continue;
        }

        $znalezione = [];

        // MAILE
        preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $html, $maile);
        foreach (array_unique($maile[0]) as $email) {
            $email = strtolower(trim($email));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

            $check = $pdo->prepare("SELECT id FROM kontakty WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() == 0) {
                $stmt = $pdo->prepare("INSERT INTO kontakty (email, strona_url) VALUES (?, ?)");
                $stmt->execute([$email, $url]);
                $znalezione[] = "<span style='color:green; font-weight:bold;'>Email: $email (dodano)</span>";
            } else {
                $znalezione[] = "<span style='color:#777; font-style:italic;'>Email: $email (już w bazie)</span>";
            }
        }

        // TELEFONY – XXX XXX XXX
        preg_match_all('/(\+48\s?)?(\d{3}[\s-]?){2}\d{3}\b/', $html, $telefony);
        $telefony = array_unique($telefony[0]);

        foreach ($telefony as $tel) {
            $tel = trim($tel);
            if (empty($tel)) continue;

            $tel_formatted = normalizujTelefon($tel);
            if (empty($tel_formatted)) continue;

            $check = $pdo->prepare("SELECT id FROM kontakty WHERE telefon = ?");
            $check->execute([$tel_formatted]);
            
            if ($check->rowCount() == 0) {
                $stmt = $pdo->prepare("INSERT INTO kontakty (telefon, strona_url) VALUES (?, ?)");
                $stmt->execute([$tel_formatted, $url]);
                $znalezione[] = "<span style='color:green; font-weight:bold;'>Telefon: $tel_formatted (dodano)</span>";
            } else {
                $znalezione[] = "<span style='color:#777; font-style:italic;'>Telefon: $tel_formatted (już w bazie)</span>";
            }
        }

        $wyniki[$url] = $znalezione 
            ? implode('<br>', $znalezione)
            : "<span style='color:orange;'>Nic nie znaleziono</span>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scraper, Dodaj i analizuj</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 10px; }
        .nav { text-align: center; margin-bottom: 30px; }
        .nav a { margin: 0 15px; color: #3498db; font-weight: bold; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #3498db; color: white; }
        .form-row { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
        input[type="url"] { flex: 1; padding: 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 6px; }
        button { padding: 12px 24px; font-size: 16px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-add { background: #27ae60; color: white; }
        .btn-scrape { background: #e67e22; color: white; font-weight: bold; }
        .btn-delete { background: #c0392b; color: white; font-size: 12px; padding: 6px 12px; }
        .btn-clear-all { background: #dc3545; color: white; font-weight: bold; }
        .alert { padding: 12px; margin: 15px 0; border-radius: 6px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .result { margin: 15px 0; padding: 15px; background: #f1f3f5; border-left: 5px solid #3498db; border-radius: 6px; }
        .actions { display: flex; gap: 10px; justify-content: center; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Scraper Kontaktów</h2>
    <div class="nav">
        <a href="index.php">Strona główna</a> |
        <a href="panel.php">Zobacz wyniki</a>
    </div>

    <?php if (isset($dodano)): ?>
        <div class="alert success">Dodano URL!</div>
    <?php endif; ?>
    <?php if (isset($blad)): ?>
        <div class="alert error"><?= $blad ?></div>
    <?php endif; ?>
    <?php if (isset($usunieto_wszystkie)): ?>
        <div class="alert success">Usunięto wszystkie URL-e!</div>
    <?php endif; ?>

    <h3>Dodaj stronę do analizy</h3>
    <form method="post" class="form-row">
        <input type="url" name="url" placeholder="https://solidnaksiegowa.com" required>
        <button type="submit" name="add_url" class="btn-add">Dodaj stronę!</button>
    </form>

    <h3>Strony w kolejce (<?= count($urls) ?>)</h3>
    <?php if (empty($urls)): ?>
        <p style="color:#7f8c8d;"><i>Brak stron. Dodaj powyżej.</i></p>
    <?php else: ?>
    <table>
        <tr><th>#</th><th>URL</th><th>Akcja</th></tr>
        <?php foreach ($urls as $i => $url): ?>
        <tr>
            <td><strong><?= $i + 1 ?></strong></td>
            <td><a href="<?= $url ?>" target="_blank"><?= htmlspecialchars($url) ?></a></td>
            <td><a href="?usun=<?= $i ?>" class="btn-delete" onclick="return confirm('Usunąć?')">Usuń</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="actions">
        <form method="post" onsubmit="return confirm('Na pewno usunąć WSZYSTKIE URL-e?');">
            <button type="submit" name="usun_wszystkie" class="btn-clear-all">
                Usuń wszystkie
            </button>
        </form>
        <form method="post">
            <button type="submit" name="scrape" class="btn-scrape">
                Scrape teraz! (<?= count($urls) ?> stron)
            </button>
        </form>
    </div>
    <?php endif; ?>

    <?php if (!empty($wyniki)): ?>
        <h3>Wyniki scrapingu:</h3>
        <?php foreach ($wyniki as $url => $wynik): ?>
            <div class="result">
                <strong><?= htmlspecialchars($url) ?></strong><br>
                <?= $wynik ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>