<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM Note WHERE pubblica = 1");
$stmt->execute();
$result = $stmt->get_result();
$notePubbliche = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Home - Nota Bene</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <div class="navbar">
    <div class="site-title">Nota Bene</div>
    <div class="profile">
      <p>Utente: <span><?= htmlspecialchars($_SESSION['utente']) ?></span></p>
      <a href="profile.php">Profilo</a> |
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<div class="container">
  <section class="welcome-section">
    <h1>Benvenuto su Nota Bene</h1>
  </section>

  <section class="notes-section">
    <div style="display:flex; justify-content: space-between; align-items: center;">
      <h2>Note pubbliche</h2>
      <a href="newnote.php" class="btn-new-note">+ Nuova Nota</a>
    </div>
    <div class="notes-content">
      <aside class="tags-sidebar">
        <h3>Tag</h3>
        <ul class="tag-list">
          <!-- Tag dinamici opzionali -->
        </ul>
        <h3>Cartelle</h3>
        <ul class="tag-list">
          <!-- Cartelle dinamiche opzionali -->
        </ul>
      </aside>
      <div class="notes-list">
        <?php foreach ($notePubbliche as $nota): ?>
          <div class="note-card">
            <p><?= nl2br(htmlspecialchars($nota['testo'])) ?></p>
            <p><strong>Autore:</strong> <?= htmlspecialchars($nota['autore']) ?></p>
            <p><strong>Tag:</strong> <?= htmlspecialchars($nota['tag']) ?></p>
            <p><strong>Cartella:</strong> <?= htmlspecialchars($nota['cartella']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>
