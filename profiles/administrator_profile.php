<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrator') {
	header('Location: login.php');
	exit;
}

if (isset($_SESSION['user_id'])) {
	$administrator_id = $_SESSION['user_id'];

	$query = "SELECT * FROM administrator WHERE administrator_id = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param('i', $administrator_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows === 1) {
		$administrator = $result->fetch_assoc();
	} else {
	}

	$stmt->close();
} else {
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
	<h2>Administrator Profile</h2>
	<?php if (isset($administrator)) { ?>
	    <div class="card">
		<div class="card-body">
		    <h5 class="card-title">Administrator Details</h5>
		    <p class="card-text">Administrator ID: <?php echo $administrator['administrator_id']; ?></p>
		    <p class="card-text">First Name: <?php echo $administrator['first_name']; ?></p>
		    <p class="card-text">Surname: <?php echo $administrator['surname']; ?></p>
		    <p class="card-text">Email Address: <?php echo $administrator['email_address']; ?></p>
		    <!-- Display other administrator details here -->
		</div>
	    </div>

	    <div class="mt-3">
		<h4>Actions:</h4>
		<ul class="list-group">
		    <li class="list-group-item active">
			<h3>Registrations</h3>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_patient.php">Register a Patient</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_doctor.php">Register a Doctor</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_pharmacy.php">Register a Pharmacy</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_pharmacist.php">Register a Pharmacist</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_administrator.php">Register Administrator</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_category.php">Register a Category</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_pharmaceutical.php">Register a Pharmaceutical</a>
		    </li>
		    <li class="list-group-item">
			<a href="../registration/register_supervisor.php">Register a Supervisor</a>
		    </li>
		    <li class="list-group-item active">
			<h3>Records</h3>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_patients.php">View Registered Patients</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_pharmacies.php">View Registered Pharmacies</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_pharmacists.php">View Registered Pharmacists</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_administrators.php">View Registered Administrators</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_categories.php">View Registered Categories</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_contracts.php">View Registered Contracts</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_doctors.php">View Registered Doctors</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_drugs.php">View Registered Drugs</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_pharmaceuticals.php">View Registered Pharmaceuticals</a>
		    </li>
		    <li class="list-group-item">
			<a href="../records/registered_supervisors.php">View Registered Supervisors</a>
		    </li>
		</ul>
	    </div>
	<?php } else { ?>
	    <p>Administrator not found.</p>
	<?php } ?>
    </div>
</body>
</html>
