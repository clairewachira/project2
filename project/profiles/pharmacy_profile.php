<?php
session_start();
include('../header.php');

if (!isset($_SESSION['user_type'])) {
	header('Location: ../login/login.php');
	exit();
}

require_once('../credentials.php');

$connection = mysqli_connect($database_host, $database_user, $database_password, $database_name);

function sanitize($data)
{
	global $connection;
	return mysqli_real_escape_string($connection, htmlspecialchars(trim($data)));
}

function format_date($date)
{
	return date('D d, F Y', strtotime($date));
}

$errors = array(); // Array to store validation errors

if (isset($_GET['pharmacy_id'])) {
	$pharmacy_id = sanitize($_GET['pharmacy_id']);

	$query_pharmacy = "SELECT * FROM pharmacy WHERE pharmacy_id = '$pharmacy_id'";
	$result_pharmacy = mysqli_query($connection, $query_pharmacy);
	$pharmacy = mysqli_fetch_assoc($result_pharmacy);

	$query_pharmacists = "SELECT * FROM pharmacist WHERE pharmacy_id = '$pharmacy_id'";
	$result_pharmacists = mysqli_query($connection, $query_pharmacists);
	$pharmacists = mysqli_fetch_all($result_pharmacists, MYSQLI_ASSOC);

	$query_contracts = "SELECT contract.*, pharmaceutical.name AS pharmaceutical_name
		FROM contract
		INNER JOIN pharmaceutical ON contract.pharmaceutical_id = pharmaceutical.pharmaceutical_id
		WHERE contract.pharmacy_id = '$pharmacy_id'";
	$result_contracts = mysqli_query($connection, $query_contracts);
	$contracts = mysqli_fetch_all($result_contracts, MYSQLI_ASSOC);
} else {
	$errors[] = "Pharmacy ID not provided";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Profile</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/min/moment.min.js"></script>
</head>
<body>
    <div class="container mt-5">
	<?php if (!empty($errors)) : ?>
	    <div class="alert alert-danger" role="alert">
		<ul>
		    <?php foreach ($errors as $error) : ?>
			<li><?php echo $error; ?></li>
		    <?php endforeach; ?>
		</ul>
	    </div>
	<?php endif; ?>
	<?php if (isset($pharmacy)) : ?>
	    <h2 class="text-center text-maroon mb-4">Pharmacy Profile</h2>
	    <div class="row" style = "border-radius: 10px; border: solid 1px grey;">
		<div class="col-md-9">
		    <div class="mb-4">
			<h3><?php echo $pharmacy['name']; ?></h3>
			<p class="mb-0">Location: <?php echo $pharmacy['location']; ?></p>
			<p class="mb-0">Email: <?php echo $pharmacy['email_address']; ?></p>
			<p class="mb-0">Phone: <?php echo $pharmacy['phone_number']; ?></p>
		    </div>
		</div>
	    </div>

	    <div class="mb-4">
		<h4 class="text-maroon mb-3">Associated Pharmacists</h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th>Name</th>
			    <th>Email</th>
			    <th>Phone</th>
			</tr>
		    </thead>
		    <tbody>
			<?php foreach ($pharmacists as $pharmacist) : ?>
			    <tr>
				<td><a href="../profiles/pharmacist_profile.php?pharmacist_id=<?php echo $pharmacist['pharmacist_id']; ?>"><?php echo $pharmacist['first_name'] . ' ' . $pharmacist['surname']; ?></a></td>
				<td><?php echo $pharmacist['email_address']; ?></td>
				<td><?php echo $pharmacist['phone_number']; ?></td>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
		</table>
	    </div>

	    <div class="mb-4">
		<h4 class="text-maroon mb-3">Associated Contracts</h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th>Contract ID</th>
			    <th>Pharmaceutical</th>
			    <th>Start Date</th>
			    <th>End Date</th>
			    <th>Ongoing</th>
			</tr>
		    </thead>
		    <tbody>
			<?php foreach ($contracts as $contract) : ?>
			    <tr>
				<td><a href="../profiles/contract_profile.php?contract_id=<?php echo $contract['contract_id']; ?>"><?php echo $contract['contract_id']; ?></a></td>
				<td><a href="../profiles/pharmaceutical_profile.php?pharmaceutical_id=<?php echo $contract['pharmaceutical_id']; ?>"><?php echo $contract['pharmaceutical_name']; ?></a></td>
				<td><?php echo format_date($contract['start_date']); ?></td>
				<td><?php echo format_date($contract['end_date']); ?></td>
				<td><?php echo $contract['end_date'] >= date('Y-m-d') ? 'Yes' : 'No'; ?></td>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
		</table>
	    </div>
	<?php endif; ?>
    </div>
</body>
</html>
