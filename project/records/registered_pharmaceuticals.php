<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT pharmaceutical_id, name, location, email_address FROM pharmaceutical";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Pharmaceuticals</h2>';
	echo '<table class="table table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Pharmaceutical ID</th>';
	echo '<th>Name</th>';
	echo '<th>Location</th>';
	echo '<th>Email Address</th>';
	echo '<th>Profile</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td>' . $row['pharmaceutical_id'] . '</td>';
		echo '<td>' . $row['name'] . '</td>';
		echo '<td>' . $row['location'] . '</td>';
		echo '<td>' . $row['email_address'] . '</td>';
		echo '<td><a href="../profiles/pharmaceutical_profile.php?pharmaceutical_id=' . $row['pharmaceutical_id'] . '">View Profile</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
} else {
	echo '<div class="container mt-5">';
	echo '<p>No pharmaceuticals found.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
