<?php
session_start();
require_once('../header.php');

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

if (isset($_GET['physician_id'])) {
	$physician_id = sanitize($_GET['physician_id']);

	$query_physician = "SELECT * FROM physician WHERE physician_id = '$physician_id'";
	$result_physician = mysqli_query($connection, $query_physician);
	$physician = mysqli_fetch_assoc($result_physician);

	$query_patients = "SELECT * FROM patient WHERE patient_id NOT IN (SELECT patient_id FROM patient_physician WHERE physician_id = '$physician_id')";
	$result_patients = mysqli_query($connection, $query_patients);
	$patients = mysqli_fetch_all($result_patients, MYSQLI_ASSOC);

	function get_patient_name($patient)
	{
		return $patient['first_name'] . ' ' . $patient['surname'];
	}

	if (isset($_POST['assign_patient'])) {
		$patient_id = sanitize($_POST['patient_id']);
		$is_primary = isset($_POST['is_primary']) ? 1 : 0;

		if (empty($patient_id)) {
			$errors[] = "Please select a patient to assign.";
		} else {
			if ($is_primary) {
				$query_primary = "SELECT * FROM patient_physician WHERE physician_id = '$physician_id' AND is_primary = 1";
				$result_primary = mysqli_query($connection, $query_primary);
				if (mysqli_num_rows($result_primary) > 0) {
					$errors[] = "Physician can have only one primary patient.";
				}
			}

			if (empty($errors)) {
				$query_assign_patient = "INSERT INTO patient_physician (patient_id, physician_id, is_primary) VALUES ('$patient_id', '$physician_id', '$is_primary')";
				if (mysqli_query($connection, $query_assign_patient)) {
					$success = "Patient assigned successfully.";
				} else {
					$errors[] = "Failed to assign patient.";
				}
			}
		}
	}

	$query_assigned_patients = "SELECT patient.*, patient_physician.is_primary
		FROM patient
		INNER JOIN patient_physician ON patient.patient_id = patient_physician.patient_id
		WHERE patient_physician.physician_id = '$physician_id'";
	$result_assigned_patients = mysqli_query($connection, $query_assigned_patients);
	$assigned_patients = mysqli_fetch_all($result_assigned_patients, MYSQLI_ASSOC);
} else {
	$errors[] = "Physician ID not provided";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physician Profile</title>
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
	<?php if (isset($physician)) : ?>
	    <h2 class="text-center text-maroon mb-4">Doctor Profile</h2>
	    <div class="d-flex justify-content-center mb-4">
		<div class="rounded-circle bg-secondary" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
<?php
$image_url = "../" . $physician['image_url'];
if (file_exists($image_url)) {
	echo '<img src="' . $image_url . '" alt="Physician Image" style="max-width: 100%; max-height: 100%; border-radius: 50%;">';
} else {
	echo '<span class="text-white">No Profile Image</span>';
}
?>
		</div>
	    </div>
	    <div class="mb-4 text-center">
		<h3><?php echo $physician['first_name'] . ' ' . $physician['surname']; ?></h3>
		<p class="mb-0">Gender: <?php echo $physician['gender']; ?></p>
		<p class="mb-0">Email: <?php echo $physician['email_address']; ?></p>
		<p class="mb-0">Phone: <?php echo $physician['phone_number']; ?></p>
	    </div>

	    <?php if ($_SESSION['user_type'] === 'administrator' || $_SESSION['user_id'] === $physician_id) : ?>
		<div class="mb-4">
		    <h4 class="text-maroon mb-3">Assign Patients</h4>
		    <form method="POST">
			<div class="row mb-3">
			    <div class="col-md-6">
				<select class="form-select" name="patient_id" required>
				    <option value="" selected disabled>Select a patient</option>
				    <?php foreach ($patients as $patient) : ?>
					<option value="<?php echo $patient['patient_id']; ?>"><?php echo get_patient_name($patient); ?></option>
				    <?php endforeach; ?>
				</select>
			    </div>
			    <div class="col-md-4">
				<div class="form-check">
				    <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary">
				    <label class="form-check-label" for="is_primary">Primary Patient</label>
				</div>
			    </div>
			    <div class="col-md-2">
				<button type="submit" class="btn btn-primary" name="assign_patient">Assign</button>
			    </div>
			</div>
		    </form>
		</div>

		<div class="mb-4">
		    <h4 class="text-maroon mb-3">Assigned Patients</h4>
		    <table class="table table-hover">
			<thead>
			    <tr>
				<th>Patient</th>
				<th>Date of Birth</th>
				<th>Gender</th>
				<th>Primary/Secondary</th>
			    </tr>
			</thead>
			<tbody>
<?php
foreach ($assigned_patients as $assigned_patient) :
	$patient_url = "../profiles/patient_profile.php?patient_id=" . $assigned_patient['patient_id'];
?>
				<tr>
				    <td><a href="<?php echo $patient_url; ?>"><?php echo get_patient_name($assigned_patient); ?></a></td>
				    <td><?php echo format_date($assigned_patient['date_of_birth']); ?></td>
				    <td><?php echo $assigned_patient['gender']; ?></td>
				    <td><?php echo $assigned_patient['is_primary'] ? 'Primary' : 'Secondary'; ?></td>
				</tr>
			    <?php endforeach; ?>
			</tbody>
		    </table>
		</div>
	    <?php endif; ?>

	<?php endif; ?>
    </div>
</body>
</html>
