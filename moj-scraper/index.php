<!DOCTYPE html>
<html>
<head>
    <title>Scraper Kontaktów, Portfolio</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial; text-align: center; padding: 60px; background: #f4f7fa; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        p { color: #7f8c8d; font-size: 18px; margin-bottom: 40px; }
        .btn { 
            display: inline-block; 
            padding: 16px 36px; 
            background: #3498db; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
            margin: 0 10px;
        }
        .btn:hover { background: #2980b9; transform: translateY(-2px); }
        .footer { margin-top: 60px; color: #95a5a6; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Scraper Kontaktów, Portfolio @blazejeh</h1>
    <p>Zbieraj maile i telefony z biur księgowych. Zapisuj do bazy. Eksportuj do CSV.</p>
    
    <a href="scraper.php" class="btn">Rozpocznij scraping</a>
    <a href="panel.php" class="btn" style="background:#27ae60;">Zobacz wyniki</a>

    <div class="footer">
        <p>PHP + MySQL + cURL | 2025</p>
    </div>
</body>
</html>