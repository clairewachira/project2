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

if (isset($_GET['pharmacist_id'])) {
	$pharmacist_id = sanitize($_GET['pharmacist_id']);

	$query_pharmacist = "SELECT * FROM pharmacist WHERE pharmacist_id = '$pharmacist_id'";
	$result_pharmacist = mysqli_query($connection, $query_pharmacist);
	$pharmacist = mysqli_fetch_assoc($result_pharmacist);

	$pharmacy_id = $pharmacist['pharmacy_id'];
	$query_pharmacy = "SELECT name FROM pharmacy WHERE pharmacy_id = '$pharmacy_id'";
	$result_pharmacy = mysqli_query($connection, $query_pharmacy);
	$pharmacy_name = mysqli_fetch_assoc($result_pharmacy)['name'];

	$query_prescriptions = "SELECT prescription.*, drug.*, physician.first_name AS physician_first_name, physician.surname AS physician_surname, patient.first_name AS patient_first_name, patient.surname AS patient_surname
		FROM prescription
		INNER JOIN patient_physician ON prescription.patient_physician_id = patient_physician.patient_physician_id
		INNER JOIN patient ON patient_physician.patient_id = patient.patient_id
		INNER JOIN physician ON patient_physician.physician_id = physician.physician_id
		INNER JOIN drug ON prescription.drug_id = drug.drug_id
		INNER JOIN contract ON contract.contract_id = drug.contract_id
		WHERE contract.pharmacy_id = '$pharmacy_id'
		ORDER BY prescription.is_assigned DESC, prescription.prescription_id";
	$result_prescriptions = mysqli_query($connection, $query_prescriptions);
	$prescriptions = array();
	if ($result_prescriptions) {
		if (mysqli_num_rows($result_prescriptions) > 0) {
			$prescriptions = mysqli_fetch_all($result_prescriptions, MYSQLI_ASSOC);
		} else {
			echo "No prescriptions found.";
		}
	} else {
		echo "Error executing the query: " . mysqli_error($connection);
	}
} else {
	$errors[] = "Pharmacist ID not provided";
}

function get_doctor_name($doctor)
{
	return $doctor['physician_first_name'] . ' ' . $doctor['physician_surname'];
}

function get_patient_name($patient)
{
	return $patient['patient_first_name'] . ' ' . $patient['patient_surname'];
}

function is_prescription_assigned($prescription)
{
	return $prescription['is_assigned'] == 1;
}

if (isset($_POST['assign_prescription']) && isset($_POST['prescription_id'])) {
	$prescription_id = sanitize($_POST['prescription_id']);
	$is_assigned = is_prescription_assigned(mysqli_fetch_assoc(mysqli_query($connection, "SELECT is_assigned FROM prescription WHERE prescription_id = '$prescription_id'")));


	if ($is_assigned) {
		mysqli_query($connection, "UPDATE prescription SET is_assigned = 0 WHERE prescription_id = '$prescription_id'");
	} else {
		mysqli_query($connection, "UPDATE prescription SET is_assigned = 1 WHERE prescription_id = '$prescription_id'");
	}

	header("Location: pharmacist_profile.php?pharmacist_id=$pharmacist_id#prescriptions");
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Profile</title>
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
	<?php if (isset($pharmacist)) : ?>
	    <h2 class="text-center text-maroon mb-4">Pharmacist Profile</h2>
	    <div class="d-flex justify-content-center mb-4">
		<div class="rounded-circle bg-secondary" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
<?php
$image_url = "../" . $pharmacist['image_url'];
if (file_exists($image_url)) {
	echo '<img src="' . $image_url . '" alt="Pharmacist Image" style="max-width: 100%; max-height: 100%; border-radius: 50%;">';
} else {
	echo '<span class="text-white">No Profile Image</span>';
}
?>
		</div>
	    </div>
	    <div class="mb-4 text-center">
		<h3><?php echo $pharmacist['first_name'] . ' ' . $pharmacist['surname']; ?></h3>
		<p class="mb-0">Gender: <?php echo $pharmacist['gender']; ?></p>
		<p class="mb-0">Email: <?php echo $pharmacist['email_address']; ?></p>
		<p class="mb-0">Phone: <?php echo $pharmacist['phone_number']; ?></p>
		<p class="mb-0">Pharmacy: <a href="../profiles/pharmacy_profile.php?pharmacy_id=<?php echo $pharmacy_id; ?>"><?php echo $pharmacy_name; ?></a></p>
	    </div>

	    <div class="mb-4" id="prescriptions">
		<h4 class="text-maroon mb-3">Prescriptions</h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th>Doctor</th>
			    <th>Patient</th>
			    <th>Start Date</th>
			    <th>End Date</th>
			    <th>Dosage</th>
			    <th>Frequency</th>
			    <th>Cost</th>
			    <th>Status</th>
			    <th>Action</th>
			</tr>
		    </thead>
		    <tbody>
<?php
foreach ($prescriptions as $prescription) :
	$doctor_name = get_doctor_name($prescription);
$patient_name = get_patient_name($prescription);
$prescription_id = $prescription['prescription_id'];
?>
			    <tr>
				<td><?php echo $doctor_name; ?></td>
				<td><?php echo $patient_name; ?></td>
				<td><?php echo format_date($prescription['start_date']); ?></td>
				<td><?php echo format_date($prescription['end_date']); ?></td>
				<td><?php echo $prescription['dosage']; ?></td>
				<td><?php echo $prescription['frequency']; ?></td>
				<td><?php echo $prescription['cost']; ?></td>
				<td><?php echo is_prescription_assigned($prescription) ? 'Dispensed' : 'Not Dispensed'; ?></td>
				<td>
				    <form method="POST">
					<input type="hidden" name="prescription_id" value="<?php echo $prescription_id; ?>">
					<button type="submit" class="btn btn-<?php echo is_prescription_assigned($prescription) ? 'secondary' : 'success'; ?>" name="assign_prescription">
					    <?php echo is_prescription_assigned($prescription) ? 'Deactivate' : 'Assign'; ?>
					</button>
				    </form>
				</td>
			    </tr>
			<?php endforeach; ?>
		    </tbody>
		</table>
	    </div>
	<?php endif; ?>
    </div>
</body>
</html>
