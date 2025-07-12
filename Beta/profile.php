<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

$utente = $_SESSION['utente'];

$stmt = $conn->prepare("SELECT * FROM Note WHERE autore = ?");
$stmt->bind_param("s", $utente);
$stmt->execute();
$result = $stmt->get_result();
$noteUtente = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close(); 
?>


<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Profilo Utente - Nota Bene</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <div class="navbar">
    <div class="site-title">Nota Bene</div>
    <div class="profile">
      <p>Utente: <span><?= htmlspecialchars($utente) ?></span></p>
      <a href="home.php">Home</a> |
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="container">
  <h1>Le mie note</h1>
  <?php if (count($noteUtente) === 0): ?>
    <p>Non hai ancora creato note.</p>
  <?php else: ?>
    <?php foreach ($noteUtente as $nota): ?>
      <div class="note-card">
        <p><?= nl2br(htmlspecialchars($nota['testo'])) ?></p>
        <p><strong>Tag:</strong> <?= htmlspecialchars($nota['tag']) ?></p>
        <p><strong>Cartella:</strong> <?= htmlspecialchars($nota['cartella']) ?></p>
        <p><strong>Pubblica:</strong> <?= $nota['pubblica'] ? 'Sì' : 'No' ?></p>
        <p><strong>Modificabile da altri:</strong> <?= $nota['allow_edit'] ? 'Sì' : 'No' ?></p>
        <form method="post" action="delete_note.php">
          <input type="hidden" name="note_id" value="<?= $nota['id'] ?>">
          <button type="submit">Elimina</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>
