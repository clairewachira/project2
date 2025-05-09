<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT categoryId, name, description FROM category";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Categories</h2>';
	echo '<table class="table table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Category ID</th>';
	echo '<th>Name</th>';
	echo '<th>Description</th>';
	echo '<th>Category Profile</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>'; //

	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td>' . $row['categoryId'] . '</td>';
		echo '<td>' . $row['name'] . '</td>';
		echo '<td>' . $row['description'] . '</td>';
		echo '<td><a href="../profiles/category_profile.php?category_id=' . $row['categoryId'] . '">View Profile</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
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
