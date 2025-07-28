<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

$utente = $_SESSION['utente'];

// Recupero cartelle uniche
$stmt = $conn->prepare("SELECT DISTINCT cartella FROM Note WHERE autore = ?");
$stmt->bind_param("s", $utente);
$stmt->execute();
$result = $stmt->get_result();
$cartelle = [];
while ($row = $result->fetch_assoc()) {
    $c = $row['cartella'] ?: 'Senza Cartella';
    if (!in_array($c, $cartelle)) {
        $cartelle[] = $c;
    }
}
$stmt->close();

// Cartella selezionata
$cartellaSelezionata = $_GET['cartella'] ?? 'Tutte';

// Recupero note filtrate
if ($cartellaSelezionata === 'Tutte') {
    $stmt = $conn->prepare("SELECT * FROM Note WHERE autore = ?");
    $stmt->bind_param("s", $utente);
    $stmt->execute();
} elseif ($cartellaSelezionata === 'Senza Cartella') {
    $stmt = $conn->prepare("SELECT * FROM Note WHERE autore = ? AND (cartella IS NULL OR cartella = '')");
    $stmt->bind_param("s", $utente);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("SELECT * FROM Note WHERE autore = ? AND cartella = ?");
    $stmt->bind_param("ss", $utente, $cartellaSelezionata);
    $stmt->execute();
}
$noteUtente = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Profilo Utente - Nota Bene</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <style>
    .profile-container {
      display: flex;
      gap: 20px;
    }
    .tags-sidebar {
      width: 200px;
      background: #f4f4f4;
      padding: 10px;
      border-radius: 8px;
    }
    .tags-sidebar ul {
      list-style: none;
      padding: 0;
    }
    .tags-sidebar li {
      margin: 8px 0;
    }
    .tags-sidebar a {
      text-decoration: none;
      color: #333;
    }
    .notes-area {
      flex: 1;
    }
    .note-card {
      background: #fff;
      border: 1px solid #ddd;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .note-card form,
    .note-card a {
      display: inline-block;
      margin-right: 8px;
    }
    .note-card button {
      padding: 8px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      background-color: #007BFF;
      color: #fff;
      font-weight: bold;
    }
    .note-card button:hover {
      background-color: #0056b3;
    }
    .permission-section {
      margin-top: 20px;
      background-color: #f9f9f9;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .permission-section label {
      font-weight: bold;
    }
    .permission-section input[type="checkbox"] {
      margin-right: 10px;
    }
    .permission-section .permission-btn {
      background-color: #28a745;
      color: white;
    }
    .permission-section .permission-btn:hover {
      background-color: #218838;
    }
    .permission-section .permission-cancel {
      background-color: #dc3545;
      color: white;
    }
    .permission-section .permission-cancel:hover {
      background-color: #c82333;
    }
  </style>
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

<main class="container profile-container">
  <aside class="tags-sidebar">
    <h3>Cartelle</h3>
    <ul>
      <li><a href="profile.php?cartella=Tutte">Tutte</a></li>
      <?php foreach ($cartelle as $c): ?>
        <li><a href="profile.php?cartella=<?= urlencode($c) ?>"><?= htmlspecialchars($c) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <section class="notes-area">
    <h1>Note: <?= htmlspecialchars($cartellaSelezionata) ?></h1>
    <?php if (count($noteUtente) === 0): ?>
      <p>Nessuna nota presente in questa cartella.</p>
    <?php else: ?>
      <?php foreach ($noteUtente as $nota): ?>
        <div class="note-card">
          <p><strong>Titolo:</strong> <?= htmlspecialchars($nota['titolo']) ?></p>
          <p><?= nl2br(htmlspecialchars($nota['testo'])) ?></p>
          <p><strong>Tag:</strong> <?= htmlspecialchars($nota['tag']) ?></p>
          <p><strong>Cartella:</strong> <?= $nota['cartella'] ?: 'Senza Cartella' ?></p>
          <p><strong>Pubblica:</strong> <?= $nota['pubblica'] ? 'Sì' : 'No' ?></p>
          <p><strong>Modificabile da altri:</strong> <?= $nota['allow_edit'] ? 'Sì' : 'No' ?></p>

          <!-- Sezione permessi -->
          <div class="permission-section">
            <h3>Gestione Permessi</h3>
            <form method="post" class="update-permissions" data-id="<?= $nota['id'] ?>">
              <label for="scrittura_permessi_<?= $nota['id'] ?>">Permessi di scrittura:</label>
              <input type="checkbox" name="scrittura_permessi" id="scrittura_permessi_<?= $nota['id'] ?>" <?= $nota['allow_edit'] ? 'checked' : '' ?>> Consentire scrittura<br><br>

              <button type="submit" class="permission-btn">Aggiorna Permessi</button>
            </form>
          </div>

          <!-- Pulsante Modifica -->
          <a href="form_modifica.php?id=<?= $nota['id'] ?>" class="btn-edit">Modifica</a>

          <!-- Pulsante Elimina -->
          <form method="post" action="delete_note.php" style="display:inline;">
            <input type="hidden" name="note_id" value="<?= $nota['id'] ?>">
            <button type="submit" class="permission-cancel">Elimina</button>
          </form>

          <!-- Pulsante Rendi Pubblica/Privata -->
          <form method="post" action="toggle_privacy.php" style="display:inline;">
            <input type="hidden" name="note_id" value="<?= $nota['id'] ?>">
            <input type="hidden" name="new_status" value="<?= $nota['pubblica'] ? 0 : 1 ?>">
            <button type="submit" class="permission-btn"><?= $nota['pubblica'] ? 'Rendi Privata' : 'Rendi Pubblica' ?></button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>

<script>
  $(document).ready(function() {
    // Gestione aggiornamento dei permessi via AJAX
    $('.update-permissions').on('submit', function(event) {
      event.preventDefault();

      var noteId = $(this).data('id');
      var scritturaPermessi = $(this).find('input[name="scrittura_permessi"]').is(':checked') ? 1 : 0;

      $.ajax({
        url: 'update_permission.php',
        method: 'POST',
        data: {
          note_id: noteId,
          scrittura_permessi: scritturaPermessi
        },
        success: function(response) {
          alert('Permessi aggiornati con successo!');
        },
        error: function() {
          alert('Errore nell\'aggiornamento dei permessi.');
        }
      });
    });
  });
</script>

</body>
</html>


