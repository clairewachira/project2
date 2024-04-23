<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT supervisor.supervisor_id, supervisor.first_name, supervisor.surname, supervisor.email_address,
	supervisor.pharmaceutical_id, pharmaceutical.name AS pharmaceutical_name
	FROM supervisor
	LEFT JOIN pharmaceutical ON supervisor.pharmaceutical_id = pharmaceutical.pharmaceutical_id";

$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Supervisors</h2>';
	echo '<table class="table table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Supervisor ID</th>';
	echo '<th>First Name</th>';
	echo '<th>Surname</th>';
	echo '<th>Email Address</th>';
	echo '<th>Pharmaceutical</th>';
	echo '<th>Supervisor Profile</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td>' . $row['supervisor_id'] . '</td>';
		echo '<td>' . $row['first_name'] . '</td>';
		echo '<td>' . $row['surname'] . '</td>';
		echo '<td>' . $row['email_address'] . '</td>';
		echo '<td>' . $row['pharmaceutical_name'] . '</td>';
		echo '<td><a href="../profiles/supervisor_profile.php?supervisor_id=' . $row['supervisor_id'] . '">View Profile</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
} else {
	echo '<div class="container mt-5">';
	echo '<p>No supervisors found.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
