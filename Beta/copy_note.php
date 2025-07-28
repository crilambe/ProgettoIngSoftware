<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID della nota non specificato.");
}

$note_id = intval($_GET['id']);

// Recupera i dati della nota originale
$stmt = $conn->prepare("SELECT testo, autore, tag FROM Note WHERE id = ?");
$stmt->bind_param("i", $note_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("Nota non trovata.");
}

$nota = $result->fetch_assoc();
$stmt->close();

// Salva la copia nella cartella "Note Copiate"
$stmt_copy = $conn->prepare(
    "INSERT INTO Note (testo, autore, tag, cartella, pubblica) VALUES (?, ?, ?, 'Note Copiate', 0)"
);
$stmt_copy->bind_param("sss", $nota['testo'], $_SESSION['utente'], $nota['tag']);

if ($stmt_copy->execute()) {
    header("Location: home.php?msg=Nota copiata con successo");
} else {
    echo "Errore nella copia della nota.";
}

$stmt_copy->close();
$conn->close();
?>
