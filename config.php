<?php


function triValide($tri, $trisAutorises) {
	return in_array($tri, $trisAutorises) ? $tri : $trisAutorises[0];
}

function ordreValide($ordre) {
	return ($ordre === 'asc' || $ordre === 'desc') ? $ordre : 'asc';
}

function fleche($colonneActuelle, $tri, $ordre) {
	if ($colonneActuelle !== $tri) return '';
	$couleur = 'red';
	return $ordre === 'asc' ? "<span style='color:$couleur'>↑</span>" : "<span style='color:$couleur'>↓</span>";
}

$trisAutorises = ['name', 'picture_url', 'host_name', 'host_thumbnail_url', 'price', 'neighbourhood_group_cleansed', 'review_scores_value'];
$tri = isset($_GET['sort']) ? triValide($_GET['sort'], $trisAutorises) : $trisAutorises[0];
$ordre = isset($_GET['order']) ? ordreValide($_GET['order']) : 'asc';

try {
	$bdd = new PDO("mysql:host=localhost;dbname=airbnb;charset=utf8", "root", "");
} catch(PDOException $e) {
	die($e->getMessage());
}

$requete = "SELECT * FROM airbnb.`listings` ORDER BY $tri " . ($ordre === 'asc' ? 'ASC' : 'DESC');
$stmt = $bdd->prepare($requete);
$stmt->execute();
$donnees = $stmt->fetchAll();

function lien_th($etiquette, $colonne, $tri, $ordre) {
	$ordreSuivant = ($tri === $colonne && $ordre === 'asc') ? 'desc' : 'asc';
	$fleche = fleche($colonne, $tri, $ordre);
	$url = "?sort=$colonne&order=$ordreSuivant";
	return "<a href='$url'>$etiquette $fleche</a>";
}

?>
<table>
	<thead>
		<tr>
			<th><?= lien_th('Nom', 'name', $tri, $ordre) ?></th>
			<th><?= lien_th('Image', 'picture_url', $tri, $ordre) ?></th>
			<th><?= lien_th('Hôte', 'host_name', $tri, $ordre) ?></th>
			<th><?= lien_th("Image de l'hôte", 'host_thumbnail_url', $tri, $ordre) ?></th>
			<th><?= lien_th('Prix', 'price', $tri, $ordre) ?></th>
			<th><?= lien_th('Groupe de quartier', 'neighbourhood_group_cleansed', $tri, $ordre) ?></th>
			<th><?= lien_th('Score des avis', 'review_scores_value', $tri, $ordre) ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($donnees as $valeur) { ?>
		<tr>
			<td><?= htmlspecialchars($valeur["name"]) ?></td>
			<td><?= htmlspecialchars($valeur["picture_url"]) ?></td>
			<td><?= htmlspecialchars($valeur["host_name"]) ?></td>
			<td><?= htmlspecialchars($valeur["host_thumbnail_url"]) ?></td>
			<td><?= htmlspecialchars($valeur["price"]) ?></td>
			<td><?= htmlspecialchars($valeur["neighbourhood_group_cleansed"]) ?></td>
			<td><?= htmlspecialchars($valeur["review_scores_value"]) ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>