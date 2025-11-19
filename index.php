<?php
// Simple Pokedex - displays a welcome message, date, connects to MySQL,
// displays pokemon names and allows inserting new pokemons.

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '5^6n=Z^HEQArnKvF';
$dbName = 'pokedex';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    $dbError = "Échec de la connexion MySQL: " . $mysqli->connect_error;
} else {
    $dbError = null;
}

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $pv = isset($_POST['pv']) ? (int)$_POST['pv'] : null;

    if ($name === '') {
        $message = ['type' => 'error', 'text' => 'Le nom est requis.'];
    } else {
        if ($mysqli && !$mysqli->connect_errno) {
            $stmt = $mysqli->prepare('INSERT INTO pokemons (name, pv) VALUES (?, ?)');
           
            if ($stmt) {
                $stmt->bind_param('si', $name, $pv);
                if ($stmt->execute()) {
                    $message = ['type' => 'success', 'text' => 'Pokémon ajouté avec succès.'];
                } else {
                    $message = ['type' => 'error', 'text' => 'Erreur lors de l\'insertion: ' . $stmt->error];
                }
                $stmt->close();
            } else {
                $message = ['type' => 'error', 'text' => 'Impossible de préparer la requête: ' . $mysqli->error];
            }
        } else {
            $message = ['type' => 'error', 'text' => 'Pas de connexion à la base de données.'];
        }
    }
}

$pokemons = [];
if ($mysqli && !$mysqli->connect_errno) {
    $res = $mysqli->query('SELECT idpokemons, name, pv FROM pokemons ORDER BY idpokemons ASC');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $pokemons[] = $row;
        }
        $res->free();
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Pokedex</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; max-width:900px; margin:2rem auto; padding:0 1rem; }
        header { margin-bottom:1rem }
        form { margin-top:1rem }
        .pokemon { border-bottom:1px solid #ddd; padding:0.5rem 0 }
        .msg { padding:0.5rem; margin:0.5rem 0 }
        .msg.success { background:#e6ffed; border:1px solid #9be6a1 }
        .msg.error { background:#ffe6e6; border:1px solid #e69b9b }
        .delete-btn { color:#c00; cursor:pointer; background:none; border:0 }
    </style>
    <script>
    function deletePokemon(id) {
        if (!confirm('Supprimer ce pokémon ?')) return;
        fetch('delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id)
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('pokemon-' + id).remove();
            } else {
                alert('Erreur');
            }
        }).catch(err => alert('Erreur réseau: ' + err));
    }
    </script>
</head>
<body>
    <header>
        <h1>Pokedex</h1>
        <p>Bienvenue dans votre mini Pokedex. Aujourd'hui nous sommes le <strong><?php echo date('d/m/Y'); ?></strong>.</p>
    </header>

    <?php if ($dbError): ?>
        <div class="msg error"><?php echo htmlspecialchars($dbError); ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="msg <?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message['text']); ?>
        </div>
    <?php endif; ?>

    <section>
        <h2>Liste des pokémons</h2>
        <?php if (count($pokemons) === 0): ?>
            <p>Aucun pokémon enregistré.</p>
        <?php else: ?>
                <?php foreach ($pokemons as $p): ?>
                    <div class="pokemon" id="pokemon-<?php echo $p['idpokemons']; ?>">
                        <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                        <?php if (isset($p['pv'])): ?> — <em>PV: <?php echo (int)$p['pv']; ?></em><?php endif; ?>
                        <button class="delete-btn" onclick="deletePokemon(<?php echo $p['idpokemons']; ?>)">Supprimer</button>
                    </div>
                <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section>
        <h2>Ajouter un pokémon</h2>
        <form method="post" action="index.php">
            <div>
                <label>Nom:<br><input type="text" name="name" required></label>
            </div>
            <div>
                <label>PV (points de vie):<br><input type="number" name="pv" min="0" step="1"></label>
            </div>
            <div style="margin-top:0.5rem">
                <button type="submit">Ajouter</button>
            </div>
        </form>
    </section>

</body>
</html>