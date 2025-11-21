
<?php
// Ajout d'annonce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_listing'])) {
	$name = $_POST['name'] ?? '';
	$picture_url = $_POST['picture_url'] ?? '';
	$host_name = $_POST['host_name'] ?? '';
	$host_thumbnail_url = $_POST['host_thumbnail_url'] ?? '';
	$price = $_POST['price'] ?? '';
	$neighbourhood_group_cleansed = $_POST['neighbourhood_group_cleansed'] ?? '';
	$review_scores_value = $_POST['review_scores_value'] ?? '';

	try {
		$bdd = new PDO("mysql:host=localhost;dbname=airbnb;charset=utf8", "root", "");
		$stmt = $bdd->prepare("INSERT INTO airbnb.`listings` (name, picture_url, host_name, host_thumbnail_url, price, neighbourhood_group_cleansed, review_scores_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->execute([$name, $picture_url, $host_name, $host_thumbnail_url, $price, $neighbourhood_group_cleansed, $review_scores_value]);
		$message = "Annonce ajoutée !";
	} catch(PDOException $e) {
		$message = $e->getMessage();
	}
}

// Fonctions de tri et pagination
function triValide($tri, $trisAutorises) {
	return in_array($tri, $trisAutorises) ? $tri : $trisAutorises[0];
}
function ordreValide($ordre) {
	return ($ordre === 'asc' || $ordre === 'desc') ? $ordre : 'asc';
}

$trisAutorises = ['name', 'host_name', 'price', 'neighbourhood_group_cleansed'];
$trisLabels = [
	'name' => 'Nom',
	'host_name' => 'Propriétaire',
	'price' => 'Prix',
	'neighbourhood_group_cleansed' => 'Ville'
];
$tri = isset($_GET['sort']) ? triValide($_GET['sort'], $trisAutorises) : $trisAutorises[0];
$ordre = isset($_GET['order']) ? ordreValide($_GET['order']) : 'asc';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
	if (!isset($bdd)) {
		$bdd = new PDO("mysql:host=localhost;dbname=airbnb;charset=utf8", "root", "");
	}
} catch(PDOException $e) {
	die($e->getMessage());
}

// Compte total pour pagination
$countReq = $bdd->query("SELECT COUNT(*) FROM airbnb.`listings`");
$total = $countReq->fetchColumn();
$nbPages = ceil($total / $limit);

$requete = "SELECT * FROM airbnb.`listings` ORDER BY $tri " . ($ordre === 'asc' ? 'ASC' : 'DESC') . " LIMIT $limit OFFSET $offset";
$stmt = $bdd->prepare($requete);
$stmt->execute();
$donnees = $stmt->fetchAll();

// Formulaire d'ajout
echo '<form method="post" style="margin-bottom:2em;">';
echo '<h2>Ajouter une annonce</h2>';
echo '<input type="hidden" name="add_listing" value="1">';
echo '<label>Nom: <input type="text" name="name" required></label><br>';
echo '<label>Image (URL): <input type="url" name="picture_url" required></label><br>';
echo '<label>Propriétaire: <input type="text" name="host_name" required></label><br>';
echo '<label>Image de l\'hôte (URL): <input type="url" name="host_thumbnail_url" required></label><br>';
echo '<label>Prix: <input type="number" name="price" required></label><br>';
echo '<label>Ville: <input type="text" name="neighbourhood_group_cleansed" required></label><br>';
echo '<label>Score des avis: <input type="number" name="review_scores_value" required></label><br>';
echo '<button type="submit">Ajouter</button>';
echo '</form>';
if (!empty($message)) echo '<div style="color:green">'.htmlspecialchars($message).'</div>';

// Menu déroulant de tri
echo '<form method="get" style="margin-bottom:1em;">';
echo '<label for="sort">Trier par :</label> ';
echo '<select name="sort" id="sort">';
foreach ($trisAutorises as $col) {
	$selected = ($tri === $col) ? 'selected' : '';
	echo "<option value='$col' $selected>{$trisLabels[$col]}</option>";
}
echo '</select> ';
echo '<select name="order">';
	echo '<option value="asc"'.($ordre==='asc'?' selected':'').'>Ascendant</option>';
	echo '<option value="desc"'.($ordre==='desc'?' selected':'').'>Descendant</option>';
echo '</select> ';
echo '<button type="submit">Trier</button>';
if ($page > 1) {
	echo '<input type="hidden" name="page" value="'.$page.'">';
}
echo '</form>';

// Affichage des logements
echo '<ul style="list-style:none;padding:0;">';
foreach ($donnees as $valeur) {
	echo '<li style="margin-bottom:2em;border-bottom:1px solid #ccc;padding-bottom:1em;">';
	echo '<img src="'.htmlspecialchars($valeur["picture_url"]).'" alt="Image logement" style="max-width:200px;display:block;margin-bottom:1em;">';
	// Nom du logement sous l'image
	echo '<h3 style="margin-bottom:1em;">'.htmlspecialchars($valeur["name"]).'</h3>';
	// Toutes les infos sous le nom
	echo '<div style="margin-left:10px;">';
	echo '<p>Propriétaire : '.htmlspecialchars($valeur["host_name"]).'</p>';
	echo '<img src="'.htmlspecialchars($valeur["host_thumbnail_url"]).'" alt="Image hôte" style="max-width:80px;display:block;margin-bottom:1em;">';
	echo '<p>Prix : '.htmlspecialchars($valeur["price"]).' €</p>';
	echo '<p>Ville : '.htmlspecialchars($valeur["neighbourhood_group_cleansed"]).'</p>';
	echo '<p>Score des avis : '.htmlspecialchars($valeur["review_scores_value"]).'</p>';
	echo '</div>';
	echo '</li>';
}
echo '</ul>';

// Pagination
echo '<div style="margin-top:2em;">';
if ($page > 1) {
	$params = $_GET;
	$params['page'] = $page - 1;
	echo '<a href="?'.http_build_query($params).'">&lt; Précédent</a> ';
}
for ($i = 1; $i <= $nbPages; $i++) {
	$params = $_GET;
	$params['page'] = $i;
	$color = ($i == $page) ? 'red' : 'inherit';
	echo '<a href="?'.http_build_query($params).'" style="color:'.$color.';font-weight:bold;margin:0 4px;">'.$i.'</a>';
}
if ($page < $nbPages) {
	$params = $_GET;
	$params['page'] = $page + 1;
	echo ' <a href="?'.http_build_query($params).'">Suivant &gt;</a>';
}
echo '</div>';

?>