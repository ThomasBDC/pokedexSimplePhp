<?php
// delete.php - deletes a pokemon by id and returns JSON
header('Content-Type: application/json; charset=utf-8');

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'mdp';
$dbName = 'pokedex';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$stmt = $mysqli->prepare('DELETE FROM pokemons WHERE idpokemons = ?');
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
