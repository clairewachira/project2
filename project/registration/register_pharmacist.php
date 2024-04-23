<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$first_name = $surname = $gender = $email_address = $phone_number = $password = $image_url = $pharmacy_id = "";
$registration_success = false;
$image_upload_dir = '../static/images/pharmacists/'; // Directory where images will be uploaded

$pharmacies = array();
$query = "SELECT pharmacy_id, name FROM pharmacy";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$pharmacies[$row['pharmacy_id']] = $row['name'];
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$first_name = $_POST["first_name"];
	$surname = $_POST["surname"];
	$gender = $_POST["gender"];
	$email_address = $_POST["email_address"];
	$phone_number = $_POST["phone_number"];
	$password = $_POST["password"];
	$pharmacy_id = $_POST["pharmacy_id"];

	$hashed_password = password_hash($password, PASSWORD_DEFAULT);

	$target_file = $image_upload_dir . basename($_FILES["image"]["name"]);
	$image_upload_success = move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

	if ($image_upload_success) {
		$image_url = '../static/images/pharmacists/' . basename($_FILES["image"]["name"]);

		$query = "INSERT INTO pharmacist (first_name, surname, gender, email_address, phone_number, password_hash, image_url, pharmacy_id)
			VALUES ('$first_name', '$surname', '$gender', '$email_address', '$phone_number', '$hashed_password', '$image_url', '$pharmacy_id')";

		if ($mysqli->query($query) === TRUE) {
			$registration_success = true;
		} else {
			$_SESSION["error_message"] = "Pharmacist registration failed. Please try again later.";
		}
	} else {
		$_SESSION["error_message"] = "Image upload failed. Please try again.";
	}
}

$mysqli->close();
?>

<div class="container mt-5">
    <h2>Pharmacist Registration</h2>
<?php
if (isset($_SESSION["error_message"])) {
	echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
	unset($_SESSION["error_message"]);
}

if ($registration_success) {
	echo '<div class="alert alert-success">Pharmacist registration successful!</div>';
}
?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
	<div class="form-group">
	    <label for="first_name">First Name</label>
	    <input type="text" class="form-control" name="first_name" required value="<?php echo $first_name; ?>">
	</div>
	<div class="form-group">
	    <label for="surname">Surname</label>
	    <input type="text" class="form-control" name="surname" required value="<?php echo $surname; ?>">
	</div>
	<div class="form-group">
	    <label for="gender">Gender</label>
	    <select class="form-control" name="gender">
		<option value="Male" <?php if ($gender == 'Male') echo 'selected'; ?>>Male</option>
		<option value="Female" <?php if ($gender == 'Female') echo 'selected'; ?>>Female</option>
		<option value="Other" <?php if ($gender == 'Other') echo 'selected'; ?>>Other</option>
	    </select>
	</div>
	<div class="form-group">
	    <label for="email_address">Email Address</label>
	    <input type="email" class="form-control" name="email_address" required value="<?php echo $email_address; ?>">
	</div>
	<div class="form-group">
	    <label for="phone_number">Phone Number</label>
	    <input type="text" class="form-control" name="phone_number" value="<?php echo $phone_number; ?>">
	</div>
	<div class="form-group">
	    <label for="password">Password</label>
	    <input type="password" class="form-control" name="password" required>
	</div>
	<div class="form-group">
	    <label for="image">Image</label>
	    <input type="file" class="form-control-file" name="image" accept="image/*" required>
	</div>
	<div class="form-group">
	    <label for="pharmacy_id">Pharmacy</label>
	    <select class="form-control" name="pharmacy_id" required>
		<option value="" disabled selected>Select Pharmacy</option>
<?php
foreach ($pharmacies as $id => $pharmacy) {
	echo '<option value="' . $id . '">' . $pharmacy . '</option>';
}
?>
	    </select>
	</div>
	<button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php
include('../footer.php');
?>
