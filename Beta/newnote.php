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
    $testo = $_POST['testo'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $cartella = $_POST['cartella'] ?? '';
    $pubblica = isset($_POST['pubblica']) ? 1 : 0;
    $allow_edit = isset($_POST['allow_edit']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO Note (autore, testo, tag, cartella, pubblica, allow_edit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $autore, $testo, $tag, $cartella, $pubblica, $allow_edit);
    
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
    <textarea name="testo" rows="5" cols="50" placeholder="Scrivi la tua nota qui..." required></textarea><br><br>
    <input type="text" name="tag" placeholder="Tag (es. scuola, personale)"><br><br>
    <input type="text" name="cartella" placeholder="Cartella (es. lavoro, idee)"><br><br>
    <label><input type="checkbox" name="pubblica"> Pubblica</label><br>
    <label><input type="checkbox" name="allow_edit"> Consenti modifiche agli altri</label><br><br>
    <button type="submit">Salva nota</button>
  </form>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>
