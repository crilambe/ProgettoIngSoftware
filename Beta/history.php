<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['utente'])) { 
    header('Location: login.php'); 
    exit; 
}

$utente = $_SESSION['utente'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { 
    http_response_code(400); 
    echo "ID non valido"; 
    exit; 
}

// Recupera i dati della nota
$stmt = $conn->prepare("SELECT id, autore, titolo, testo, tag, cartella, pubblica, allow_edit FROM Note WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$nota = $stmt->get_result()->fetch_assoc();

if (!$nota) { 
    http_response_code(404); 
    echo "Nota non trovata"; 
    exit; 
}

// Controlla se l'utente può vedere la nota
$puoVedere = ($nota['autore'] === $utente) || ($nota['pubblica'] == 1);
if (!$puoVedere) { 
    http_response_code(403); 
    echo "Non hai i permessi per visualizzare questa nota."; 
    exit; 
}

// Recupera la cronologia delle revisioni
$revStmt = $conn->prepare("
  SELECT editor, created_at, titolo, testo, tag, cartella
  FROM NoteRevision
  WHERE note_id=?
  ORDER BY created_at DESC
");
$revStmt->bind_param('i', $nota['id']);
$revStmt->execute();
$revisioni = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <title>Cronologia - <?= h($nota['titolo']) ?> - Nota Bene</title>
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
    <h1>Cronologia modifiche</h1>
    <h3><?= h($nota['titolo']) ?></h3>
    <p><strong>Autore:</strong> <?= h($nota['autore']) ?></p>
    <p><strong>Stato attuale:</strong> <?= $nota['pubblica'] ? 'Pubblica' : 'Privata' ?></p>
    
    <div style="margin-top: 30px;">
      <a href="form_modifica.php?id=<?= (int)$nota['id'] ?>" class="btn-edit">← Torna alla modifica</a>
      <a href="<?= $nota['autore'] === $utente ? 'profile.php' : 'home.php' ?>" class="btn-primary" style="margin-left: 10px;">← Torna indietro</a>
    </div>

    <div style="margin-top: 30px;">
      <h2>Revisioni (<?= count($revisioni) ?>)</h2>
      
      <?php if (count($revisioni) > 0): ?>
        <?php foreach ($revisioni as $index => $revisione): ?>
          <div class="note-card" style="margin-bottom: 20px;">
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
              <h4>Revisione #<?= count($revisioni) - $index ?></h4>
              <p><strong>Modificato da:</strong> <?= h($revisione['editor']) ?></p>
              <p><strong>Data:</strong> <?= date('d/m/Y H:i:s', strtotime($revisione['created_at'])) ?></p>
            </div>
            
            <div style="margin-bottom: 15px;">
              <h5>Titolo</h5>
              <p><?= h($revisione['titolo']) ?></p>
            </div>
            
            <div style="margin-bottom: 15px;">
              <h5>Testo</h5>
              <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; white-space: pre-wrap;">
                <?= h($revisione['testo']) ?>
              </div>
            </div>
            
            <div style="display: flex; gap: 20px;">
              <div>
                <h5>Tag</h5>
                <p><?= h($revisione['tag']) ?: 'Nessun tag' ?></p>
              </div>
              <div>
                <h5>Cartella</h5>
                <p><?= h($revisione['cartella']) ?: 'Senza cartella' ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="note-card">
          <p style="text-align: center; color: #6c757d; font-style: italic;">
            Nessuna revisione registrata per questa nota.
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html> 