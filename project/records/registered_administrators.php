<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT administrator_id, first_name, surname, gender, email_address FROM administrator";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Administrators</h2>';
	echo '<table class="table table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Administrator ID</th>';
	echo '<th>First Name</th>';
	echo '<th>Surname</th>';
	echo '<th>Gender</th>';
	echo '<th>Email Address</th>';
	echo '<th>Profile</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td>' . $row['administrator_id'] . '</td>';
		echo '<td>' . $row['first_name'] . '</td>';
		echo '<td>' . $row['surname'] . '</td>';
		echo '<td>' . $row['gender'] . '</td>';
		echo '<td>' . $row['email_address'] . '</td>';
		echo '<td><a href="../profiles/administrator_profile.php?administrator_id=' . $row['administrator_id'] . '">View Profile</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
} else {
	echo '<div class="container mt-5">';
	echo '<p>No administrators found.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
