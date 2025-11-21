<?php
// Formulaire d'ajout d'annonce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_listing'])) {
    $name = $_POST['name'] ?? '';
    $picture_url = $_POST['picture_url'] ?? '';
    $host_name = $_POST['host_name'] ?? '';
    $host_thumbnail_url = $_POST['host_thumbnail_url'] ?? '';
    $price = $_POST['price'] ?? '';
    $neighbourhood_group_cleansed = $_POST['neighbourhood_group_cleansed'] ?? '';
    $review_scores_value = $_POST['review_scores_value'] ?? '';

    // Ajout simple, sans validation poussée
    try {
        $bdd = new PDO("mysql:host=localhost;dbname=airbnb;charset=utf8", "root", "");
        $stmt = $bdd->prepare("INSERT INTO airbnb.`listings` (name, picture_url, host_name, host_thumbnail_url, price, neighbourhood_group_cleansed, review_scores_value) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $picture_url, $host_name, $host_thumbnail_url, $price, $neighbourhood_group_cleansed, $review_scores_value]);
        $message = "Annonce ajoutée !";
    } catch(PDOException $e) {
        $message = $e->getMessage();
    }
}
?>
<form method="post" style="margin-bottom:2em;">
    <h2>Ajouter une annonce</h2>
    <input type="hidden" name="add_listing" value="1">
    <label>Nom: <input type="text" name="name" required></label><br>
    <label>Image (URL): <input type="url" name="picture_url" required></label><br>
    <label>Hôte: <input type="text" name="host_name" required></label><br>
    <label>Image de l'hôte (URL): <input type="url" name="host_thumbnail_url" required></label><br>
    <label>Prix: <input type="number" name="price" required></label><br>
    <label>Groupe de quartier: <input type="text" name="neighbourhood_group_cleansed" required></label><br>
    <label>Score des avis: <input type="number" name="review_scores_value" required></label><br>
    <button type="submit">Ajouter</button>
</form>
<?php if (!empty($message)) echo '<div style="color:green">'.htmlspecialchars($message).'</div>'; ?>
