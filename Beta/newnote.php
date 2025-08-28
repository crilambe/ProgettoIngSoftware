<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

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
        // Reindirizza in base al tipo di nota
        if ($pubblica) {
            // Se la nota è pubblica, vai alla home
            header("Location: home.php?msg=" . urlencode("Nota pubblica creata con successo!"));
        } else {
            // Se la nota è privata, vai al profilo
            header("Location: profile.php?msg=" . urlencode("Nota privata creata con successo!"));
        }
        exit();
    } else {
        // In caso di errore, rimani nella pagina con messaggio di errore
        $messaggio = "Errore durante il salvataggio.";
    }
    $stmt->close();
}

$messaggio = "";
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
  <div class="form-modifica">
    <h1>Crea una nuova nota</h1>

    <?php if ($messaggio): ?>
      <p id="noteMessage"><?= htmlspecialchars($messaggio) ?></p>
    <?php endif; ?>

    <form method="post">
      <label>Titolo della nota</label>
      <input type="text" name="titolo" placeholder="Titolo della nota" required>

      <label>Testo della nota</label>
      <textarea name="testo" rows="10" placeholder="Scrivi la tua nota qui..." required></textarea>

      <label>Tag</label>
      <input type="text" name="tag" placeholder="Tag (es. scuola, personale)">

      <label>Cartella</label>
      <input type="text" name="cartella" placeholder="Cartella (es. lavoro, università)">

      <div class="checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" name="pubblica"> Rendi pubblica
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="allow_edit"> Permetti modifica ad altri
        </label>
      </div>

      <button type="submit">Salva Nota</button>
    </form>
  </div>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>
