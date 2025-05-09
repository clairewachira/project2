<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$category_query = "SELECT categoryId, name FROM category";
$category_result = $mysqli->query($category_query);

if ($category_result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Categories</h2>';

	while ($category_row = $category_result->fetch_assoc()) {
		echo '<h3 class="mt-4">' . $category_row['name'] . '</h3>';

		$category_id = $category_row['categoryId'];
		$query = "SELECT drug_id, trade_name, form, image_url FROM drug WHERE categoryId = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('i', $category_id);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			echo '<div class="row">';
			while ($row = $result->fetch_assoc()) {
				echo '<div class="col-md-4 mb-4">';
				echo '<div class="card">';
				echo '<img src="../static/images/drugs/' . $row['image_url'] . '" class="card-img-top" alt="' . $row['trade_name'] . '">';
				echo '<div class="card-body">';
				echo '<h5 class="card-title">' . $row['trade_name'] . '</h5>';
				echo '<p class="card-text">Form: ' . $row['form'] . '</p>';
				echo '<a href="../profiles/drug_profile.php?drug_id=' . $row['drug_id'] . '" class="btn btn-primary">View Profile</a>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo '<p>No drugs found in this category.</p>';
		}

		$stmt->close();
	}
	echo '</div>';
} else {
	echo '<div class="container mt-5">';
	echo '<p>No categories found.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
