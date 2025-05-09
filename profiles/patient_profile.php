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

function format_date_time($date)
{
	return date('D d, F Y h:i A', strtotime($date));
}

$patient_physician_id = 0;
if ($_SESSION['user_type'] === 'physician') {
	$conn = new mysqli($database_host, $database_user, $database_password, $database_name);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} else {
		$sql = "SELECT patient_physician_id FROM patient_physician WHERE patient_id = " . $_GET['patient_id'] . " AND physician_id = " . $_SESSION['user_id'];
		$result = $conn->query($sql);
		if ($result->num_rows === 1) {
			$patient_physician_id = $result->fetch_assoc()['patient_physician_id'];
		} else {
			header("Location: ../errors/404.php");
			exit();
		}
		$conn->close();
	}
}

function displayUnassignedDoctors($patientId)
{
	$conn = new mysqli($GLOBALS['host'], $GLOBALS['username'], $GLOBALS['databasePassword'], $GLOBALS['databaseName']);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} else {
		$sql = "SELECT * FROM physician WHERE physicianId NOT IN (SELECT physicianId FROM patient_physician WHERE patientId = $patientId)";
		$result = $conn->query($sql);
		echo '<select class="form-select" name="physicianId" required>';
		echo '<option value="" selected disabled>Select a physician</option>';
		while ($row = $result->fetch_assoc()) {
			echo "<option value='{$row['physicianId']}'>{$row['name']}</option>";
		}
		echo '</select>';
		$conn->close();
	}
}


$errors = array(); // Array to store validation errors
$success = ""; // Success message

if (isset($_GET['patient_id'])) {
	$patient_id = sanitize($_GET['patient_id']);

	$query_patient = "SELECT * FROM patient WHERE patient_id = '$patient_id'";
	$result_patient = mysqli_query($connection, $query_patient);
	$patient = mysqli_fetch_assoc($result_patient);

	$query_physicians = "SELECT * FROM physician WHERE physician_id NOT IN (SELECT physician_id FROM patient_physician WHERE patient_id = '$patient_id')";
	$result_physicians = mysqli_query($connection, $query_physicians);
	$physicians = mysqli_fetch_all($result_physicians, MYSQLI_ASSOC);

	function get_physician_name($physician)
	{
		return $physician['first_name'] . ' ' . $physician['surname'];
	}

	if (isset($_POST['assign_physician'])) {
		$physician_id = sanitize($_POST['physician_id']);
		$is_primary = isset($_POST['is_primary']) ? 1 : 0;

		if (empty($physician_id)) {
			$errors[] = "Please select a physician to assign.";
		} else {
			if ($is_primary) {
				$query_primary = "SELECT * FROM patient_physician WHERE patient_id = '$patient_id' AND is_primary = 1";
				$result_primary = mysqli_query($connection, $query_primary);
				if (mysqli_num_rows($result_primary) > 0) {
					$errors[] = "Patient can have only one primary physician.";
				}
			}

			if (empty($errors)) {
				$query_assign_physician = "INSERT INTO patient_physician (patient_id, physician_id, is_primary) VALUES ('$patient_id', '$physician_id', '$is_primary')";
				if (mysqli_query($connection, $query_assign_physician)) {
					$success = "Physician assigned successfully.";
				} else {
					$errors[] = "Failed to assign physician.";
				}
			}
		}
	}

	$query_prescriptions = "SELECT prescription.*, drug.trade_name AS drug_name, physician.first_name AS physician_first_name, physician.surname AS physician_surname, pharmacy.name AS pharmacy_name
		FROM prescription
		LEFT JOIN drug ON prescription.drug_id = drug.drug_id
		LEFT JOIN patient_physician ON prescription.patient_physician_id = patient_physician.patient_physician_id
		LEFT JOIN physician ON patient_physician.physician_id = physician.physician_id
		LEFT JOIN contract ON drug.contract_id = contract.contract_id
		LEFT JOIN pharmacy ON contract.pharmacy_id = pharmacy.pharmacy_id
		WHERE patient_physician.patient_id = '$patient_id'";
	$result_prescriptions = mysqli_query($connection, $query_prescriptions);
	$prescriptions = mysqli_fetch_all($result_prescriptions, MYSQLI_ASSOC);
} else {
	$errors[] = "Patient ID not provided";
}

if ($_SESSION['user_type'] === 'physician') {
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_prescription'])) {
		$drug_id = $_POST['drug_id'];
		$start_date = $_POST['start_date'];
		$end_date = $_POST['end_date'];
		$dosage = $_POST['dosage'];
		$cost = $_POST['cost'];
		$frequency = $_POST['frequency'];

		$conn = new mysqli($database_host, $database_user, $database_password, $database_name);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		} else {
			$sql = "INSERT INTO prescription (drug_id, start_date, end_date, dosage, patient_physician_id, cost, frequency, is_assigned)
				VALUES ($drug_id, '$start_date', '$end_date', '$dosage', $patient_physician_id, $cost, '$frequency', 0)";

			if ($conn->query($sql) === TRUE) {
				header("Location: ../profiles/patient_profile.php?patient_id=" . $_GET['patient_id']);
				exit();
			} else {
				echo "Error adding prescription: " . $conn->error;
			}

			$conn->close();
		}
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
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
	<?php if (isset($patient)) : ?>
	    <h2 class="text-center text-maroon mb-4">Patient Profile</h2>
	    <div class="d-flex justify-content-center mb-4">
		<div class="rounded-circle bg-secondary" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
<?php
$image_url = "../" . $patient['image_url'];
if (file_exists($image_url)) {
	echo '<img src="' . $image_url . '" alt="Patient Image" style="max-width: 100%; max-height: 100%; border-radius: 50%;">';
} else {
	echo '<span class="text-white">No Profile Image</span>';
}
?>
		</div>
	    </div>
	    <div class="mb-4 text-center">
		<h3><?php echo $patient['first_name'] . ' ' . $patient['surname']; ?></h3>
		<p class="mb-0">Date of Birth: <?php echo format_date($patient['date_of_birth']); ?> (<?php echo floor((time() - strtotime($patient['date_of_birth'])) / 31556926); ?> years old)</p>
		<p class="mb-0">Email: <?php echo $patient['email_address']; ?></p>
		<p class="mb-0">Phone: <?php echo $patient['phone_number']; ?></p>
	    </div>

	    <?php if ($_SESSION['user_type'] === 'administrator' || $_SESSION['user_id'] === $patient_id) : ?>
		<div class="mb-4">
		    <h4 class="text-maroon mb-3">Assign Physicians</h4>
		    <form method="POST">
			<div class="row mb-3">
			    <div class="col-md-6">
				<select class="form-select" name="physician_id" required>
				    <option value="" selected disabled>Select a physician</option>
				    <?php foreach ($physicians as $physician) : ?>
					<option value="<?php echo $physician['physician_id']; ?>"><?php echo get_physician_name($physician); ?></option>
				    <?php endforeach; ?>
				</select>
			    </div>
			    <div class="col-md-4">
				<div class="form-check">
				    <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary">
				    <label class="form-check-label" for="is_primary">Primary Physician</label>
				</div>
			    </div>
			    <div class="col-md-2">
				<button type="submit" class="btn btn-primary" name="assign_physician">Assign</button>
			    </div>
			</div>
		    </form>
		</div>

		<div class="mb-4">
		    <h4 class="text-maroon mb-3">Assigned Physicians</h4>
		    <table class="table table-hover">
			<thead>
			    <tr>
				<th>Physician</th>
				<th>Specialization</th>
				<th>Primary/Secondary</th>
			    </tr>
			</thead>
			<tbody>
<?php
$query_assigned_physicians = "SELECT physician.*, patient_physician.is_primary
	FROM physician
	INNER JOIN patient_physician ON physician.physician_id = patient_physician.physician_id
	WHERE patient_physician.patient_id = '$patient_id'";
$result_assigned_physicians = mysqli_query($connection, $query_assigned_physicians);
while ($assigned_physician = mysqli_fetch_assoc($result_assigned_physicians)) :
?>
				<tr>
				    <td><?php echo get_physician_name($assigned_physician); ?></td>
				    <td><?php echo $assigned_physician['specialization']; ?></td>
				    <td><?php echo $assigned_physician['is_primary'] ? 'Primary' : 'Secondary'; ?></td>
				</tr>
			    <?php endwhile; ?>
			</tbody>
		    </table>
		</div>
	    <?php endif; ?>

	    <?php if ($_SESSION['user_type'] === 'physician') : ?>
		<!-- Prescription Assignment Section (Visible to Physician) -->
		<div class="mb-4">
		    <h4 class="text-maroon mb-3">Assign Prescription</h4>
		    <form method="POST" action = "">
			<div class="row mb-3">
			    <div class="col-md-4">
				<select class="form-select" name="drug_id" required>
				    <option value="" selected disabled>Select a drug</option>
<?php
	$query_drugs = "SELECT * FROM drug";
$result_drugs = mysqli_query($connection, $query_drugs);
while ($drug = mysqli_fetch_assoc($result_drugs)) :
?>
					<option value="<?php echo $drug['drug_id']; ?>"><?php echo $drug['trade_name']; ?></option>
				    <?php endwhile; ?>
				</select>
			    </div>
			    <div class="col-md-8">
				<div class="row">
				    <div class="col-md-6 mb-3">
					<label for="start_date" class="form-label">Start Date</label>
					<input type="date" class="form-control" id="start_date" name="start_date" required>
				    </div>
				    <div class="col-md-6 mb-3">
					<label for="end_date" class="form-label">End Date</label>
					<input type="date" class="form-control" id="end_date" name="end_date" required>
				    </div>
				</div>
			    </div>
			</div>
			<div class="row mb-3">
			    <div class="col-md-12">
				<div class="form-floating">
				    <input type = "text" class="form-control" id="dosage" name="dosage" placeholder=" " required></textarea>
				    <label for="dosage">Dosage</label>
				</div>
			    </div>
			</div>
			<div class="row mb-3">
			    <div class="col-md-12">
				<div class="form-floating">
				    <input type = "text" class="form-control" id="frequency" name="frequency" placeholder=" " required></textarea>
				    <label for="frequency">Frequency</label>
				</div>
			    </div>
			</div>
			<div class="row mb-3">
			    <div class="col-md-12">
				<div class="form-floating">
				    <input type="text" class="form-control" id="cost" name="cost" placeholder=" " required>
				    <label for="cost">Cost</label>
				</div>
			    </div>
			</div>
			<button type="submit" class="btn btn-primary" name="assign_prescription">Assign Prescription</button>
		    </form>
		</div>
	    <?php endif; ?>

	    <!-- Display Assigned Prescriptions Section -->
	    <div class="mb-4">
		<h4 class="text-maroon mb-3">Assigned Prescriptions</h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th>Physician</th>
			    <th>Drug</th>
			    <th>Start Date</th>
			    <th>End Date</th>
			    <th>Dosage</th>
			    <th>Frequency</th>
			    <th>Cost</th>
			    <?php if ($_SESSION['user_type'] === 'physician') : ?>
				<th>Status</th>
			    <?php endif; ?>
			</tr>
		    </thead>
		    <tbody>
			<?php foreach ($prescriptions as $prescription) : ?>
			    <tr>
				<td><?php echo $prescription['physician_first_name'] . ' ' . $prescription['physician_surname']; ?></td>
				<td><?php echo $prescription['drug_name']; ?></td>
				<td><?php echo format_date($prescription['start_date']); ?></td>
				<td><?php echo format_date($prescription['end_date']); ?></td>
				<td><?php echo $prescription['dosage']; ?></td>
				<td><?php echo $prescription['frequency']; ?></td>
				<td>$<?php echo number_format($prescription['cost'], 2); ?></td>
				<?php if ($_SESSION['user_type'] === 'physician') : ?>
				    <td><?php echo $prescription['is_assigned'] ? 'Dispensed' : 'Not Dispensed'; ?></td>
				<?php endif; ?>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
		</table>
	    </div>

	<?php endif; ?>
    </div>
</body>
</html>
