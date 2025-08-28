<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['utente'])) { header('Location: login.php'); exit; }
$utente = $_SESSION['utente'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { http_response_code(400); echo "ID non valido"; exit; }

$stmt = $conn->prepare("SELECT id, autore, titolo, testo, tag, cartella, pubblica, allow_edit FROM Note WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$nota = $stmt->get_result()->fetch_assoc();

if (!$nota) { http_response_code(404); echo "Nota non trovata"; exit; }

$sonoAutore = ($nota['autore'] === $utente);
$puoModificare = $sonoAutore || ($nota['pubblica'] == 1 && $nota['allow_edit'] == 1);
if (!$puoModificare) { http_response_code(403); echo "Non hai i permessi per modificare questa nota."; exit; }

// Ultima revisione (chi e quando)
$revStmt = $conn->prepare("
  SELECT editor, created_at
  FROM NoteRevision
  WHERE note_id=?
  ORDER BY created_at DESC
  LIMIT 1
");
$revStmt->bind_param('i', $nota['id']);
$revStmt->execute();
$lastRev = $revStmt->get_result()->fetch_assoc();

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Modifica nota - Nota Bene</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <div class="navbar">
    <div class="site-title">Nota Bene</div>
    <div class="profile">
      <p>Utente: <span><?= h($utente) ?></span></p>
      <a href="home.php">Home</a> |
      <a href="profile.php">Profilo</a> |
      <a href="logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="container">
  <div class="form-modifica">
    <h3><?= h($nota['titolo']) ?></h3>
    <?php if ($lastRev): ?>
      <p>
        Ultima modifica di <strong><?= h($lastRev['editor']) ?></strong>
        il <?= date('d/m/Y H:i', strtotime($lastRev['created_at'])) ?>
        — <a href="history.php?id=<?= (int)$nota['id'] ?>">cronologia</a>
      </p>
    <?php else: ?>
      <p>Nessuna modifica registrata.</p>
    <?php endif; ?>

    <form method="post" action="update_note.php">
      <input type="hidden" name="id" value="<?= (int)$nota['id'] ?>">

      <label>Titolo</label>
      <input name="titolo" required value="<?= h($nota['titolo']) ?>">

      <label>Testo</label>
      <textarea name="testo" required rows="10"><?= h($nota['testo']) ?></textarea>

      <label>Tag</label>
      <input name="tag" value="<?= h($nota['tag']) ?>">

      <label>Cartella</label>
      <input name="cartella" value="<?= h($nota['cartella']) ?>">

      <?php if ($sonoAutore): ?>
        <div class="checkbox-group">
          <label class="checkbox-label">
            <input type="checkbox" name="pubblica" <?= $nota['pubblica'] ? 'checked' : '' ?>> Pubblica
          </label>
          <label class="checkbox-label">
            <input type="checkbox" name="allow_edit" <?= $nota['allow_edit'] ? 'checked' : '' ?>> Consenti modifica ad altri
          </label>
        </div>
      <?php else: ?>
        <p>Privacy e permessi sono modificabili solo dall'autore.</p>
      <?php endif; ?>

      <button type="submit">Salva Modifiche</button>
    </form>
  </div>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>
