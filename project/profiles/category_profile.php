<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


if (isset($_GET['category_id'])) {
	$category_id = $_GET['category_id'];

	$category_query = "SELECT name FROM category WHERE categoryId = ?";
	$category_stmt = $mysqli->prepare($category_query);
	$category_stmt->bind_param('i', $category_id);
	$category_stmt->execute();
	$category_result = $category_stmt->get_result();
	$category_row = $category_result->fetch_assoc();

	$query = "SELECT drug_id, trade_name, form, image_url FROM drug WHERE categoryId = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i', $category_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		echo '<div class="container mt-5">';
		echo '<h2 class="mb-4">Category: ' . $category_row['name'] . '</h2>';

		echo '<div class="row">';
		while ($row = $result->fetch_assoc()) {
			echo '<div class="col-md-4 mb-4">';
			echo '<div class="card">';
			echo '<img src="../static/images/drugs/' . $row['image_url'] . '" class="card-img-top" alt="' . $row['trade_name'] . '">';
			echo '<div class="card-body">';
			echo '<h5 class="card-title">' . $row['trade_name'] . '</h5>';
			echo '<p class="card-text">Form: ' . $row['form'] . '</p>';
			echo '<a href="drug_profile.php?drug_id=' . $row['drug_id'] . '" class="btn btn-primary">View Profile</a>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
	} else {
		echo '<div class="container mt-5">';
		echo '<p>No drugs found in this category.</p>';
		echo '</div>';
	}

	$category_stmt->close();
	$stmt->close();
} else {
	echo '<div class="container mt-5">';
	echo '<p>Category ID not provided.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
