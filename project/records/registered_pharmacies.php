<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT pharmacy_id, name, location, email_address FROM pharmacy";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Pharmacies</h2>';
	echo '<table class="table table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Pharmacy ID</th>';
	echo '<th>Name</th>';
	echo '<th>Location</th>';
	echo '<th>Email Address</th>';
	echo '<th>Profile</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td>' . $row['pharmacy_id'] . '</td>';
		echo '<td>' . $row['name'] . '</td>';
		echo '<td>' . $row['location'] . '</td>';
		echo '<td>' . $row['email_address'] . '</td>';
		echo '<td><a href="../profiles/pharmacy_profile.php?pharmacy_id=' . $row['pharmacy_id'] . '">View Profile</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
} else {
	echo '<div class="container mt-5">';
	echo '<p>No pharmacies found.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
