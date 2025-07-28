<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $autore = $_SESSION['utente'];
    $titolo = $_POST['titolo'] ?? ''; // Titolo della nota
    $testo = $_POST['testo'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $cartella = $_POST['cartella'] ?? '';
    $pubblica = isset($_POST['pubblica']) ? 1 : 0;
    $allow_edit = isset($_POST['allow_edit']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO Note (autore, titolo, testo, tag, cartella, pubblica, allow_edit) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssii", $autore, $titolo, $testo, $tag, $cartella, $pubblica, $allow_edit);
    
    if ($stmt->execute()) {
        $messaggio = "Nota salvata con successo.";
    } else {
        $messaggio = "Errore durante il salvataggio.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Nuova Nota - Nota Bene</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <div class="navbar">
    <div class="site-title">Nota Bene</div>
    <div class="profile">
      <p>Utente: <span><?= htmlspecialchars($_SESSION['utente']) ?></span></p>
      <a href="home.php">Home</a> |
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="container">
  <h1>Crea una nuova nota</h1>

  <?php if ($messaggio): ?>
    <p><?= htmlspecialchars($messaggio) ?></p>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="titolo" class="form-control" placeholder="Titolo della nota" required><br>
    <textarea name="testo" rows="5" class="form-control" placeholder="Scrivi la tua nota qui..." required></textarea><br>
    <input type="text" name="tag" class="form-control" placeholder="Tag (es. scuola, personale)"><br>
    <input type="text" name="cartella" class="form-control" placeholder="Cartella (es. lavoro, università)"><br>

    <div class="checkbox-group">
      <label class="checkbox-label">
        <input type="checkbox" name="pubblica"> Rendi pubblica
      </label>
      <label class="checkbox-label">
        <input type="checkbox" name="allow_edit"> Permetti modifica ad altri
      </label>
    </div>
    <br> <button type="submit" class="add-note-btn">Salva Nota</button>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>
