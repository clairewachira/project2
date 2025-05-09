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

if (isset($_GET['pharmaceutical_id'])) {
	$pharmaceutical_id = sanitize($_GET['pharmaceutical_id']);

	$query_pharmaceutical = "SELECT * FROM pharmaceutical WHERE pharmaceutical_id = '$pharmaceutical_id'";
	$result_pharmaceutical = mysqli_query($connection, $query_pharmaceutical);
	$pharmaceutical = mysqli_fetch_assoc($result_pharmaceutical);

	$query_supervisors = "SELECT * FROM supervisor WHERE pharmaceutical_id = '$pharmaceutical_id'";
	$result_supervisors = mysqli_query($connection, $query_supervisors);
	$supervisors = mysqli_fetch_all($result_supervisors, MYSQLI_ASSOC);
} else {
	$errors[] = "Pharmaceutical ID not provided";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmaceutical Profile</title>
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
	<?php if (isset($pharmaceutical)) : ?>
	    <h2 class="text-center text-maroon mb-4">Pharmaceutical Profile</h2>
	    <div class="row" style = "border-radius: 10px; border: solid 1px grey;">
		<div class="col-md-9">
		    <div class="mb-4">
			<h3><?php echo $pharmaceutical['name']; ?></h3>
			<p class="mb-0">Location: <?php echo $pharmaceutical['location']; ?></p>
			<p class="mb-0">Email: <?php echo $pharmaceutical['email_address']; ?></p>
			<p class="mb-0">Phone: <?php echo $pharmaceutical['phone_number']; ?></p>
		    </div>
		</div>
	    </div>

	    <div class="mb-4">
		<h4 class="text-maroon mb-3">Associated Supervisors</h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th>Name</th>
			    <th>Email</th>
			    <th>Phone</th>
			</tr>
		    </thead>
		    <tbody>
			<?php foreach ($supervisors as $supervisor) : ?>
			    <tr>
				<td><a href="../profiles/supervisor_profile.php?supervisor_id=<?php echo $supervisor['supervisor_id']; ?>"><?php echo $supervisor['first_name'] . ' ' . $supervisor['surname']; ?></a></td>
				<td><?php echo $supervisor['email_address']; ?></td>
				<td><?php echo $supervisor['phone_number']; ?></td>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
		</table>
	    </div>
	<?php endif; ?>
    </div>
</body>
</html>
