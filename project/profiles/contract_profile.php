<?php
session_start();
require_once('../header.php');

if (!isset($_SESSION['user_type'])) {
	header('Location: ../login/login.php');
	exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contract Profile</title>
    <!-- Add Bootstrap CSS link here or your preferred CSS for styling -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
	<div class="row">
	    <div class="col-md-8 offset-md-2">
		<h2 class="my-4 text-center">Contract Profile</h2>
<?php
require_once('../credentials.php');

$conn = mysqli_connect($database_host, $database_user, $database_password, $database_name);

function get_contract_details($conn, $contract_id) {
	$stmt = $conn->prepare("SELECT * FROM contract WHERE contract_id = ?");
	$stmt->bind_param("i", $contract_id);
	$stmt->execute();
	$result = $stmt->get_result();
	return $result->fetch_assoc();
}

function get_pharmacy_name($conn, $pharmacy_id) {
	$stmt = $conn->prepare("SELECT name FROM pharmacy WHERE pharmacy_id = ?");
	$stmt->bind_param("i", $pharmacy_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	return $row['name'];
}

function get_pharmaceutical_name($conn, $pharmaceutical_id) {
	$stmt = $conn->prepare("SELECT name FROM pharmaceutical WHERE pharmaceutical_id = ?");
	$stmt->bind_param("i", $pharmaceutical_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	return $row['name'];
}

if (!isset($_GET['contract_id'])) {
	echo "<p class='text-danger'>Contract ID not provided.</p>";
	exit();
}

$contract_id = intval($_GET['contract_id']);

$contract_details = get_contract_details($conn, $contract_id);

if (!$contract_details) {
	echo "<p class='text-danger'>Contract not found.</p>";
	exit();
}

$pharmacy_name = get_pharmacy_name($conn, $contract_details['pharmacy_id']);
$pharmaceutical_name = get_pharmaceutical_name($conn, $contract_details['pharmaceutical_id']);

function get_registered_drugs($conn, $contract_id) {
	$stmt = $conn->prepare("SELECT * FROM drug WHERE contract_id = ?");
	$stmt->bind_param("i", $contract_id);
	$stmt->execute();
	$result = $stmt->get_result();
	return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$registered_drugs = get_registered_drugs($conn, $contract_id);

$conn->close();
?>
		<div class="card mb-4">
		    <div class="card-body">
			<h5 class="card-title">Contract Details</h5>
			<p>Contract ID: <?php echo $contract_details['contract_id']; ?></p>
			<p>Pharmacy: <a href="../profiles/pharmacy_profile.php?pharmacy_id=<?php echo $contract_details['pharmacy_id']; ?>"><?php echo $pharmacy_name; ?></a></p>
			<p>Pharmaceutical: <a href="../profiles/pharmaceutical_profile.php?pharmaceutical_id=<?php echo $contract_details['pharmaceutical_id']; ?>"><?php echo $pharmaceutical_name; ?></a></p>
			<p>Period: <?php echo $contract_details['start_date']; ?> to <?php echo $contract_details['end_date']; ?></p>
			<p>Status: <?php echo (strtotime($contract_details['end_date']) >= time()) ? "Ongoing" : "Expired"; ?></p>
		    </div>
		    <div class="card-footer">
			<a href="../registration/register_drug.php?contract_id=<?php echo $contract_details['contract_id']; ?>" class="btn btn-info">Add Drug</a>
		    </div>
		</div>
		<h4 class="my-3">Registered Drugs</h4>
		<?php if (empty($registered_drugs)) : ?>
		    <p>No drugs registered for this contract.</p>
		<?php else : ?>
		    <table class="table table-hover">
			<thead class="thead-light">
			    <tr>
				<th>Scientific Name</th>
				<th>Trade Name</th>
				<th>Expiry Date</th>
				<th>Manufacturing Date</th>
				<th>Amount</th>
				<th>Form</th>
			    </tr>
			</thead>
			<tbody>
			    <?php foreach ($registered_drugs as $drug) : ?>
				<tr>
				    <td><?php echo $drug['scientific_name']; ?></td>
				    <td><?php echo $drug['trade_name']; ?></td>
				    <td><?php echo $drug['expiry_date']; ?></td>
				    <td><?php echo $drug['manufacturing_date']; ?></td>
				    <td><?php echo $drug['amount']; ?></td>
				    <td><?php echo $drug['form']; ?></td>
				</tr>
			    <?php endforeach; ?>
			</tbody>
		    </table>
		<?php endif; ?>
	    </div>
	</div>
    </div>
    <!-- Add Bootstrap JS and jQuery script links here -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.1/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
