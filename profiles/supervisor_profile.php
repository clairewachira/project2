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
$success = ""; // Success message

if (isset($_GET['supervisor_id'])) {
	$supervisor_id = sanitize($_GET['supervisor_id']);

	$query_supervisor = "SELECT * FROM supervisor WHERE supervisor_id = '$supervisor_id'";
	$result_supervisor = mysqli_query($connection, $query_supervisor);
	$supervisor = mysqli_fetch_assoc($result_supervisor);

	$pharmaceutical_id = $supervisor['pharmaceutical_id'];
	$query_pharmaceutical = "SELECT name FROM pharmaceutical WHERE pharmaceutical_id = '$pharmaceutical_id'";
	$result_pharmaceutical = mysqli_query($connection, $query_pharmaceutical);
	$pharmaceutical_name = mysqli_fetch_assoc($result_pharmaceutical)['name'];

	$query_contracts = "SELECT contract.*, pharmacy.name AS pharmacy_name
		FROM contract
		INNER JOIN pharmacy ON contract.pharmacy_id = pharmacy.pharmacy_id
		WHERE contract.pharmaceutical_id = '$pharmaceutical_id'";
	$result_contracts = mysqli_query($connection, $query_contracts);
	$contracts = mysqli_fetch_all($result_contracts, MYSQLI_ASSOC);

	$query_pharmacies = "SELECT * FROM pharmacy";
	$result_pharmacies = mysqli_query($connection, $query_pharmacies);
	$pharmacies = mysqli_fetch_all($result_pharmacies, MYSQLI_ASSOC);
} else {
	$errors[] = "Supervisor ID not provided";
}

function get_pharmacy_name($pharmacy)
{
	return $pharmacy['name'];
}

function is_valid_contract_period($start_date, $end_date)
{
	$start = strtotime($start_date);
	$end = strtotime($end_date);
	return $start !== false && $end !== false && $start <= $end;
}

if (isset($_POST['create_contract'])) {
	$pharmacy_id = sanitize($_POST['pharmacy_id']);
	$start_date = sanitize($_POST['start_date']);
	$end_date = sanitize($_POST['end_date']);

	if (empty($pharmacy_id) || empty($start_date) || empty($end_date)) {
		$errors[] = "All fields are required.";
	} elseif (!is_valid_contract_period($start_date, $end_date)) {
		$errors[] = "Invalid contract period. The start date must be before or equal to the end date.";
	} else {
		$query_create_contract = "INSERT INTO contract (start_date, end_date, pharmacy_id, pharmaceutical_id) VALUES ('$start_date', '$end_date', '$pharmacy_id', '$pharmaceutical_id')";
		if (mysqli_query($connection, $query_create_contract)) {
			$success = "New contract created successfully.";
			header('Location: ../profiles/supervisor_profile.php?supervisor_id=' . $supervisor_id);
			exit();
		} else {
			$errors[] = "Failed to create a new contract.";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Profile</title>
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
	<?php if ($success) : ?>
	    <div class="alert alert-success" role="alert">
		<?php echo $success; ?>
	    </div>
	<?php endif; ?>
	<?php if (isset($supervisor)) : ?>
	    <h2 class="text-center text-maroon mb-4">Supervisor Profile</h2>
	    <div class="d-flex justify-content-center mb-4">
		<div class="rounded-circle bg-secondary" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
<?php
$image_url = "../" . $supervisor['image_url'];
if (file_exists($image_url)) {
	echo '<img src="' . $image_url . '" alt="Supervisor Image" style="max-width: 100%; max-height: 100%; border-radius: 50%;">';
} else {
	echo '<span class="text-white">No Profile Image</span>';
}
?>
		</div>
	    </div>
	    <div class="mb-4 text-center">
		<h3><?php echo $supervisor['first_name'] . ' ' . $supervisor['surname']; ?></h3>
		<p class="mb-0">Gender: <?php echo $supervisor['gender']; ?></p>
		<p class="mb-0">Email: <?php echo $supervisor['email_address']; ?></p>
		<p class="mb-0">Phone: <?php echo $supervisor['phone_number']; ?></p>
		<p class="mb-0">Pharmaceutical: <a href="../profiles/pharmaceutical_profile.php?pharmaceutical_id=<?php echo $pharmaceutical_id; ?>"><?php echo $pharmaceutical_name; ?></a></p>
	    </div>

	    <div class="mb-4">
		<h4 class="text-maroon mb-3">Contracts</h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th>Contract ID</th>
			    <th>Pharmacy</th>
			    <th>Start Date</th>
			    <th>End Date</th>
			    <th>Status</th>
			</tr>
		    </thead>
		    <tbody>
<?php
foreach ($contracts as $contract) :
	$contract_url = "../profiles/contract_profile.php?contract_id=" . $contract['contract_id'];
?>
			    <tr>
				<td><a href="<?php echo $contract_url; ?>"><?php echo $contract['contract_id']; ?></a></td>
				<td><a href="../profiles/pharmacy_profile.php?pharmacy_id=<?php echo $contract['pharmacy_id']; ?>"><?php echo $contract['pharmacy_name']; ?></a></td>
				<td><?php echo format_date($contract['start_date']); ?></td>
				<td><?php echo format_date($contract['end_date']); ?></td>
				<td><?php echo (strtotime($contract['end_date']) >= time()) ? 'Ongoing' : 'Expired'; ?></td>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
		</table>
	    </div>

	    <?php if ($_SESSION['user_type'] === 'administrator') : ?>
		<div class="mb-4">
		    <h4 class="text-maroon mb-3">Create New Contract</h4>
		    <form method="POST">
			<div class="row mb-3">
			    <div class="col-md-6">
				<label for="pharmacy_id" class="form-label">Select Pharmacy</label>
				<select class="form-select" name="pharmacy_id" required>
				    <option value="" selected disabled>Select a pharmacy</option>
				    <?php foreach ($pharmacies as $pharmacy) : ?>
					<option value="<?php echo $pharmacy['pharmacy_id']; ?>"><?php echo get_pharmacy_name($pharmacy); ?></option>
				    <?php endforeach; ?>
				</select>
			    </div>
			    <div class="col-md-3">
				<label for="start_date" class="form-label">Start Date</label>
				<input type="date" class="form-control" name="start_date" required>
			    </div>
			    <div class="col-md-3">
				<label for="end_date" class="form-label">End Date</label>
				<input type="date" class="form-control" name="end_date" required>
			    </div>
			</div>
			<div class="row mb-3">
			    <div class="col-md-12">
				<button type="submit" class="btn btn-primary" name="create_contract">Create Contract</button>
			    </div>
			</div>
		    </form>
		</div>
	    <?php endif; ?>

	<?php endif; ?>
    </div>
</body>
</html>
