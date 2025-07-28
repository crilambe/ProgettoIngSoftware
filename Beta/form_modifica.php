<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID nota mancante.");
}

$noteId = intval($_GET['id']);
$utente = $_SESSION['utente'];

// Recupero nota
$stmt = $conn->prepare("SELECT * FROM Note WHERE id = ? AND autore = ?");
$stmt->bind_param("is", $noteId, $utente);
$stmt->execute();
$result = $stmt->get_result();
$nota = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$nota) {
    die("Nota non trovata o accesso non autorizzato.");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Modifica Nota</title>
  <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
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
  <section class="new-note-section">
    <h1>Modifica Nota</h1>
    <form action="update_note.php" method="post">
      <input type="hidden" name="note_id" value="<?= $nota['id'] ?>">

      <label>Testo:</label>
      <textarea name="testo" rows="5" class="form-control" required><?= htmlspecialchars($nota['testo']) ?></textarea>

      <label>Tag:</label>
      <input type="text" name="tag" value="<?= htmlspecialchars($nota['tag']) ?>" class="form-control">

      <label>Cartella:</label>
      <input type="text" name="cartella" value="<?= htmlspecialchars($nota['cartella']) ?>" class="form-control">

      <div class="checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" name="pubblica" <?= $nota['pubblica'] ? 'checked' : '' ?>> Rendi pubblica
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="allow_edit" <?= $nota['allow_edit'] ? 'checked' : '' ?>> Permetti modifica ad altri
        </label>
      </div>
      <br>

      <button type="submit" class="btn-primary save-note-btn">Salva Modifiche</button>
    </form>
    <p><a href="profile.php" class="btn-primary" style="margin-top: 20px;">Torna al Profilo</a></p>
  </section>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>