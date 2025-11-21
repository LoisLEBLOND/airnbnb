
<?php
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

$countReq = $bdd->query("SELECT COUNT(*) FROM airbnb.`listings`");
$total = $countReq->fetchColumn();
$nbPages = ceil($total / $limit);

$requete = "SELECT * FROM airbnb.`listings` ORDER BY $tri " . ($ordre === 'asc' ? 'ASC' : 'DESC') . " LIMIT $limit OFFSET $offset";
$stmt = $bdd->prepare($requete);
$stmt->execute();
$donnees = $stmt->fetchAll();


echo '<link rel="stylesheet" href="style.css">';

echo '<div class="config-page">';
echo '<div class="config-wrapper">';

echo '<aside class="card form-card">';
echo '<form method="post" class="create-form">';
echo '<h2>Ajouter une annonce</h2>';
echo '<p class="sub">Remplissez les informations ci-dessous pour créer une nouvelle annonce.</p>';
echo '<input type="hidden" name="add_listing" value="1">';

echo '<div class="form-grid">';
echo '<div class="form-field"><label>Nom</label><input type="text" name="name" required></div>';
echo '<div class="form-field"><label>Prix (€)</label><input type="number" name="price" required></div>';
echo '<div class="form-field"><label>Image (URL)</label><input type="url" name="picture_url" required></div>';
echo '<div class="form-field"><label>Ville</label><input type="text" name="neighbourhood_group_cleansed" required></div>';
echo '<div class="form-field"><label>Propriétaire</label><input type="text" name="host_name" required></div>';
echo '<div class="form-field"><label>Image de l\'hôte (URL)</label><input type="url" name="host_thumbnail_url" required></div>';
echo '<div class="form-field full-width"><label>Score des avis</label><input type="number" name="review_scores_value" min="0" max="10" required></div>';
echo '</div>'; 

echo '<div class="form-actions">';
echo '<button class="btn btn-primary" type="submit">Ajouter</button>';
echo '<button class="btn btn-ghost" type="reset">Annuler</button>';
echo '</div>';
echo '</form>';
if (!empty($message)) echo '<div class="form-message">'.htmlspecialchars($message).'</div>';
echo '</aside>';

echo '<section class="card listings-card">';
echo '<div class="listings-header">';
echo '<h3>Annonces</h3>';
echo '<form method="get" class="sort-form">';
echo '<label for="sort">Trier :</label>';
echo '<select name="sort" id="sort">';
foreach ($trisAutorises as $col) {
	$selected = ($tri === $col) ? 'selected' : '';
	echo "<option value='$col' $selected>{$trisLabels[$col]}</option>";
}
echo '</select>';
echo '<select name="order">';
	echo '<option value="asc"'.($ordre==='asc'?' selected':'').'>Ascendant</option>';
	echo '<option value="desc"'.($ordre==='desc'?' selected':'').'>Descendant</option>';
echo '</select>';
if ($page > 1) {
	echo '<input type="hidden" name="page" value="'.$page.'">';
}
echo '<button class="btn btn-ghost" type="submit">Trier</button>';
echo '</form>';
echo '</div>';


echo '<ul class="listings-grid">';
foreach ($donnees as $valeur) {
	echo '<li class="listing-item">';
	echo '<div class="listing-body">';
		echo '<h4 class="listing-title">'.htmlspecialchars($valeur["name"]).'</h4>';
		echo '<div class="listing-thumb"><img src="'.htmlspecialchars($valeur["picture_url"]).'" alt="Image logement"></div>';
		echo '<div class="host-block">';
			echo '<span class="host-thumb"><img src="'.htmlspecialchars($valeur["host_thumbnail_url"]).'" alt="Hôte"></span>';
			echo '<div class="host-name muted">'.htmlspecialchars($valeur["host_name"]).'</div>';
			echo '</div>';
		    echo '<div class="meta" style="margin-top:10px;display:flex;gap:12px;align-items:center;">';
			echo '<div class="listing-price">Prix: '.htmlspecialchars($valeur["price"]).' €</div>';
			echo '<div class="listing-meta">Ville: '.htmlspecialchars($valeur["neighbourhood_group_cleansed"]).'</div>';
			echo '<div class="score muted">Score: '.htmlspecialchars($valeur["review_scores_value"]).'</div>';
			echo '</div>';
		echo '</div>';
		echo '</li>';
}
echo '</ul>';

echo '<div class="pagination">';
if ($page > 1) {
	$params = $_GET;
	$params['page'] = $page - 1;
	echo '<a class="btn btn-ghost" href="?'.http_build_query($params).'">&lt; Précédent</a>';
}
for ($i = 1; $i <= $nbPages; $i++) {
	$params = $_GET;
	$params['page'] = $i;
	$active = ($i == $page) ? 'active' : '';
	echo '<a class="'. $active .'" href="?'.http_build_query($params).'">'.$i.'</a>';
}
if ($page < $nbPages) {
	$params = $_GET;
	$params['page'] = $page + 1;
	echo '<a class="btn btn-ghost" href="?'.http_build_query($params).'">Suivant &gt;</a>';
}
echo '</div>';

echo '</section>'; 

echo '</div>'; 
echo '</div>'; 
?>