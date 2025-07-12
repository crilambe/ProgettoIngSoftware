<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['utente'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['note_id'])) {
    $noteId = intval($_POST['note_id']);
    $utente = $_SESSION['utente'];

    // Verifica che la nota appartenga all'utente
    $check = $conn->prepare("SELECT * FROM Note WHERE id = ? AND autore = ?");
    $check->bind_param("is", $noteId, $utente);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $testo = $_POST['testo'] ?? '';
        $tag = $_POST['tag'] ?? '';
        $cartella = $_POST['cartella'] ?? '';
        $pubblica = isset($_POST['pubblica']) ? 1 : 0;
        $allow_edit = isset($_POST['allow_edit']) ? 1 : 0;

        $update = $conn->prepare("UPDATE Note SET testo = ?, tag = ?, cartella = ?, pubblica = ?, allow_edit = ? WHERE id = ?");
        $update->bind_param("ssssii", $testo, $tag, $cartella, $pubblica, $allow_edit, $noteId);
        $update->execute();
        $update->close();
    }
    $check->close();
}

$conn->close();
header("Location: profile.php");
exit();
?>