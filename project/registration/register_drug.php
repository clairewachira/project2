<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


if (isset($_GET['contract_id']) && is_numeric($_GET['contract_id'])) {
	$contract_id = $_GET['contract_id'];
} else {
	$_SESSION["error_message"] = "Invalid contract_id.";
	header("Location: index.php"); // Redirect to an appropriate page
	exit();
}

$scientific_name = $trade_name = $expiry_date = $manufacturing_date = $amount = $form = "";
$image_url = $category_id = $registration_success = false;

$categories = array();
$query = "SELECT categoryId, name FROM category";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$categories[$row['categoryId']] = $row['name'];
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$scientific_name = $_POST["scientific_name"];
	$trade_name = $_POST["trade_name"];
	$expiry_date = $_POST["expiry_date"];
	$manufacturing_date = $_POST["manufacturing_date"];
	$amount = $_POST["amount"];
	$form = $_POST["form"];
	$category_id = $_POST['category_id'];

	if ($_FILES["image_upload"]["error"] === 0) {
		$image_name = $_FILES["image_upload"]["name"];
		$image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

		$unique_image_name = uniqid('drug_') . '.' . $image_extension;

		$target_directory = '../static/images/drugs/';

		$target_file = $target_directory . $unique_image_name;

		$is_image = getimagesize($_FILES["image_upload"]["tmp_name"]);
		if ($is_image) {
			if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $target_file)) {
				$query = "INSERT INTO drug (scientific_name, trade_name, expiry_date, manufacturing_date, amount, form, image_url, contract_id, categoryId)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

				if ($stmt = $mysqli->prepare($query)) {
					$stmt->bind_param("ssssisssi", $scientific_name, $trade_name, $expiry_date, $manufacturing_date, $amount, $form, 
						$unique_image_name, $contract_id, $category_id);

					if ($stmt->execute()) {
						$registration_success = true;
					} else {
						$_SESSION["error_message"] = "Drug registration failed. Please try again later.";
						echo $stmt->error;
					}

					$stmt->close();
				}

			} else {
				$_SESSION["error_message"] = "File upload failed. Please try again.";
			}
		} else {
			$_SESSION["error_message"] = "Please upload a valid image file.";
		}
	} else {
		$_SESSION["error_message"] = "Please select an image to upload.";
	}
}

$mysqli->close();
?>

<div class="container mt-5">
    <h2>Drug Registration</h2>
<?php
if (isset($_SESSION["error_message"])) {
	echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
	unset($_SESSION["error_message"]);
}

if ($registration_success) {
	echo '<div class="alert alert-success">Drug registration successful!</div>';
}
?>

	<form method="post" action="../registration/register_drug.php?contract_id=<?php echo $contract_id; ?>" enctype="multipart/form-data">
	<div class="form-group">
	    <label for="scientific_name">Scientific Name</label>
	    <input type="text" class="form-control" name="scientific_name" required value="<?php echo $scientific_name; ?>">
	</div>
	<div class="form-group">
	    <label for="trade_name">Trade Name</label>
	    <input type="text" class="form-control" name="trade_name" required value="<?php echo $trade_name; ?>">
	</div>
	<div class="form-group">
	    <label for="expiry_date">Expiry Date</label>
	    <input type="date" class="form-control" name="expiry_date" required value="<?php echo $expiry_date; ?>">
	</div>
	<div class="form-group">
	    <label for="manufacturing_date">Manufacturing Date</label>
	    <input type="date" class="form-control" name="manufacturing_date" required value="<?php echo $manufacturing_date; ?>">
	</div>
	<div class="form-group">
	    <label for="amount">Amount</label>
	    <input type="number" class="form-control" name="amount" required value="<?php echo $amount; ?>">
	</div>
	<div class="form-group">
	    <label for="form">Form</label>
	    <input type="text" class="form-control" name="form" required value="<?php echo $form; ?>">
	</div>
	<div class="form-group">
	    <label for="image_upload">Upload Image</label>
	    <input type="file" class="form-control-file" name="image_upload" accept="image/*" required>
	</div>
	<div class="form-group">
	    <label for="category_id">Category</label>
	    <select class="form-control" name="category_id" required>
		<option value="" disabled selected>Select Category</option>
<?php
foreach ($categories as $id => $category) {
	echo '<option value="' . $id . '">' . $category . '</option>';
}
?>
	    </select>
	</div>
	<input type="hidden" name="contract_id" value="<?php echo $contract_id; ?>">
	<button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php
include('../footer.php');
?>
