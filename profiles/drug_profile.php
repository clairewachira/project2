<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['drug_id'])) {
	$drug_id = $_GET['drug_id'];

	$query = "SELECT d.drug_id, d.scientific_name, d.trade_name, d.expiry_date, d.manufacturing_date, d.amount,
		d.form, d.image_url, c.name AS category_name
		FROM drug AS d
		INNER JOIN category AS c ON d.categoryId = c.categoryId
		WHERE d.drug_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i', $drug_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows === 1) {
		$row = $result->fetch_assoc();
		echo '<div class="container mt-5">';
		echo '<h2>Drug Profile</h2>';
		echo '<div class="card mt-4">';
		echo '<div class="row g-0">';
		echo '<div class="col-md-4">';
		echo '<img src="../static/images/drugs/' . $row['image_url'] . '" class="img-fluid" alt="' . $row['trade_name'] . '">';
		echo '</div>';
		echo '<div class="col-md-8">';
		echo '<div class="card-body">';
		echo '<h5 class="card-title">' . $row['trade_name'] . '</h5>';
		echo '<p class="card-text">Scientific Name: ' . $row['scientific_name'] . '</p>';
		echo '<p class="card-text">Expiry Date: ' . $row['expiry_date'] . '</p>';
		echo '<p class="card-text">Manufacturing Date: ' . $row['manufacturing_date'] . '</p>';
		echo '<p class="card-text">Amount: ' . $row['amount'] . '</p>';
		echo '<p class="card-text">Form: ' . $row['form'] . '</p>';
		echo '<p class="card-text">Category: ' . $row['category_name'] . '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	} else {
		echo '<div class="container mt-5">';
		echo '<p>Drug not found.</p>';
		echo '</div>';
	}

	$stmt->close();
} else {
	echo '<div class="container mt-5">';
	echo '<p>Drug ID not provided.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
