<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['utente'])) { header('Location: login.php'); exit; }
$utente = $_SESSION['utente'];

// (facoltativo ma utile in dev: mostra errori mysqli)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
$titolo   = trim($_POST['titolo']   ?? '');
$testo    = trim($_POST['testo']    ?? '');
$tag      = trim($_POST['tag']      ?? '');
$cartella = trim($_POST['cartella'] ?? '');

if ($id <= 0 || $titolo === '' || $testo === '') {
  http_response_code(400);
  echo "Parametri non validi";
  exit;
}

// Carica stato attuale della nota
$stmt = $conn->prepare("SELECT id, autore, pubblica, allow_edit FROM Note WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$nota = $stmt->get_result()->fetch_assoc();

if (!$nota) {
  http_response_code(404);
  echo "Nota non trovata";
  exit;
}

$sonoAutore       = ($nota['autore'] === $utente);
$collabConsentita = ($nota['pubblica'] == 1 && $nota['allow_edit'] == 1);

if (!$sonoAutore && !$collabConsentita) {
  http_response_code(403);
  echo "Non hai i permessi per modificare questa nota.";
  exit;
}

if ($sonoAutore) {
  // L'autore può cambiare tutto (compresi i flag)
  $pubblica   = isset($_POST['pubblica']) ? 1 : 0;
  $allow_edit = isset($_POST['allow_edit']) ? 1 : 0;

  $sql = "UPDATE Note 
          SET titolo=?, testo=?, tag=?, cartella=?, pubblica=?, allow_edit=?
          WHERE id=? AND autore=?";
  $upd = $conn->prepare($sql);
  // tipi: s s s s i i i s  -> 'ssssiiis'
  $upd->bind_param('ssssiiis', $titolo, $testo, $tag, $cartella, $pubblica, $allow_edit, $id, $utente);
  $upd->execute();

} else {
  // Collaboratore: può cambiare solo contenuti, e solo se la nota resta pubblica e editabile
  $sql = "UPDATE Note 
          SET titolo=?, testo=?, tag=?, cartella=?
          WHERE id=? AND pubblica=1 AND allow_edit=1";
  $upd = $conn->prepare($sql);
  // tipi: s s s s i -> 'ssssi'
  $upd->bind_param('ssssi', $titolo, $testo, $tag, $cartella, $id);
  $upd->execute();
}

// Registra la revisione
$rev = $conn->prepare("
  INSERT INTO NoteRevision (note_id, editor, titolo, testo, tag, cartella)
  VALUES (?,?,?,?,?,?)
");
// tipi: i s s s s s -> 'isssss'
$rev->bind_param('isssss', $id, $utente, $titolo, $testo, $tag, $cartella);
$rev->execute();

// Reindirizza in base al tipo di nota
if ($sonoAutore) {
    // Se l'utente è l'autore, controlla se la nota è pubblica o privata
    if ($pubblica) {
        // Se la nota è pubblica, vai alla home
        header("Location: home.php?msg=" . urlencode("Nota pubblica aggiornata da $utente"));
    } else {
        // Se la nota è privata, vai al profilo
        header("Location: profile.php?msg=" . urlencode("Nota privata aggiornata da $utente"));
    }
} else {
    // Se è un collaboratore, la nota è sempre pubblica, quindi vai alla home
    header("Location: home.php?msg=" . urlencode("Nota aggiornata da $utente"));
}
exit;
