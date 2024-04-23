<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT pharmacist.pharmacist_id, pharmacist.first_name, pharmacist.surname,
	pharmacist.email_address, pharmacy.name AS pharmacy_name, pharmacy.pharmacy_id
	FROM pharmacist
	LEFT JOIN pharmacy ON pharmacist.pharmacy_id = pharmacy.pharmacy_id";

$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	echo '<div class="container mt-5">';
	echo '<h2>Registered Pharmacists</h2>';
	echo '<table class="table table-bordered">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>Pharmacist ID</th>';
	echo '<th>First Name</th>';
	echo '<th>Surname</th>';
	echo '<th>Email Address</th>';
	echo '<th>Pharmacy</th>';
	echo '<th>Pharmacist Profile</th>';
	echo '<th>Pharmacy Profile</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	while ($row = $result->fetch_assoc()) {
		echo '<tr>';
		echo '<td>' . $row['pharmacist_id'] . '</td>';
		echo '<td>' . $row['first_name'] . '</td>';
		echo '<td>' . $row['surname'] . '</td>';
		echo '<td>' . $row['email_address'] . '</td>';
		echo '<td>' . $row['pharmacy_name'] . '</td>';
		echo '<td><a href="../profiles/pharmacist_profile.php?pharmacist_id=' . $row['pharmacist_id'] . '">View Pharmacist Profile</a></td>';
		echo '<td><a href="../profiles/pharmacy_profile.php?pharmacy_id=' . $row['pharmacy_id'] . '">View Pharmacy</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
} else {
	echo '<div class="container mt-5">';
	echo '<p>No pharmacists found.</p>';
	echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
