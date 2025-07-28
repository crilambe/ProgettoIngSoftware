<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Modifica Nota</h1>
  <form action="update_note.php" method="post">
    <input type="hidden" name="note_id" value="<?= $nota['id'] ?>">
    
    <label>Testo:</label><br>
    <textarea name="testo" rows="5" cols="50"><?= htmlspecialchars($nota['testo']) ?></textarea><br><br>
    
    <label>Tag:</label><br>
    <input type="text" name="tag" value="<?= htmlspecialchars($nota['tag']) ?>"><br><br>
    
    <label>Cartella:</label><br>
    <input type="text" name="cartella" value="<?= htmlspecialchars($nota['cartella']) ?>"><br><br>
    
    <label>
      <input type="checkbox" name="pubblica" <?= $nota['pubblica'] ? 'checked' : '' ?>> Pubblica
    </label><br>
    <label>
      <input type="checkbox" name="allow_edit" <?= $nota['allow_edit'] ? 'checked' : '' ?>> Modificabile da altri
    </label><br><br>
    
    <button type="submit">Salva Modifiche</button>
  </form>
  <p><a href="profile.php">Torna al Profilo</a></p>
</body>
</html>
