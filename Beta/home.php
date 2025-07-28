<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

// Controllo della ricerca
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchDate = isset($_GET['date']) ? $_GET['date'] : '';

// Preparazione della query di ricerca
$query = "SELECT * FROM Note WHERE pubblica = 1";

if ($searchTerm || $searchDate) {
    $query .= " AND (";
    $conditions = [];

    // Ricerca per parole
    if ($searchTerm) {
        $searchTerm = "%" . $searchTerm . "%";
        $conditions[] = "titolo LIKE ? OR testo LIKE ? OR tag LIKE ? OR cartella LIKE ?";
    }

    // Ricerca per data (creazione o modifica)
    if ($searchDate) {
        $conditions[] = "(data_creazione BETWEEN ? AND ? OR data_ultima_modifica BETWEEN ? AND ?)";
    }

    $query .= implode(" OR ", $conditions) . ")";
}

$stmt = $conn->prepare($query);

// Binding dei parametri
if ($searchTerm || $searchDate) {
    $types = '';
    $params = [];

    // Binding per parole
    if ($searchTerm) {
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }

    // Binding per data
    if ($searchDate) {
        $startDate = $searchDate . " 00:00:00";
        $endDate = $searchDate . " 23:59:59";
        $params[] = $startDate;
        $params[] = $endDate;
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= 'ssss';
    }

    $stmt->bind_param($types, ...$params);
}

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

    <!-- Modulo di ricerca -->
    <form method="GET" class="search-form">
      <input type="text" name="search" placeholder="Cerca per titolo, testo, tag o cartella..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
      <input type="date" name="date" value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>" />
      <button type="submit">Cerca</button>
    </form>

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
        <?php if (count($notePubbliche) > 0): ?>
          <?php foreach ($notePubbliche as $nota): ?>
            <div class="note-card">
              <h3><?= htmlspecialchars($nota['titolo']) ?></h3>
              <p><?= nl2br(htmlspecialchars($nota['testo'])) ?></p>
              <p><strong>Autore:</strong> <?= htmlspecialchars($nota['autore']) ?></p>
              <p><strong>Tag:</strong> <?= htmlspecialchars($nota['tag']) ?></p>
              <p><strong>Cartella:</strong> <?= htmlspecialchars($nota['cartella']) ?></p>
              <p><strong>Data di creazione:</strong> <?= htmlspecialchars($nota['data_creazione']) ?></p>
              <p><strong>Ultima modifica:</strong> <?= htmlspecialchars($nota['data_ultima_modifica']) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>Nessun risultato trovato per la tua ricerca.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<footer>
  <small>&copy; 2025 Nota Bene - Università di Bologna</small>
</footer>
</body>
</html>



