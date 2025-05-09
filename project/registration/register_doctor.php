<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$first_name = $surname = $gender = $email_address = $phone_number = $medical_license_number = $hospital_name = $specialization = $password = $image_url = "";
$registration_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$first_name = $_POST["first_name"];
	$surname = $_POST["surname"];
	$gender = $_POST["gender"];
	$email_address = $_POST["email_address"];
	$phone_number = $_POST["phone_number"];
	$medical_license_number = $_POST["medical_license_number"];
	$hospital_name = $_POST["hospital_name"];
	$specialization = $_POST["specialization"];
	$password = $_POST["password"];

	if ($_FILES["image_upload"]["error"] === 0) {
		$image_name = $_FILES["image_upload"]["name"];
		$image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

		$unique_image_name = uniqid('doctor_') . '.' . $image_extension;

		$target_directory = '../static/images/doctors/';

		$target_file = $target_directory . $unique_image_name;

		$is_image = getimagesize($_FILES["image_upload"]["tmp_name"]);
		if ($is_image) {
			if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $target_file)) {
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);

				$query = "INSERT INTO physician (first_name, surname, gender, email_address, phone_number, medical_licence_number, hospital_name, specialization, image_url, password_hash)
					VALUES ('$first_name', '$surname', '$gender', '$email_address', '$phone_number', '$medical_license_number', '$hospital_name', '$specialization', '$unique_image_name', '$hashed_password')";

				if ($mysqli->query($query) === TRUE) {
					$registration_success = true;
				} else {
					$_SESSION["error_message"] = "Doctor registration failed. Please try again later.";
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
    <h2>Doctor Registration</h2>
<?php
if (isset($_SESSION["error_message"])) {
	echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
	unset($_SESSION["error_message"]);
}

if ($registration_success) {
	echo '<div class="alert alert-success">Doctor registration successful!</div>';
}
?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
	<!-- Add form fields for doctor registration -->
	<div class="form-group">
	    <label for="first_name">First Name</label>
	    <input type="text" class="form-control" name="first_name" required value="<?php echo $first_name; ?>">
	</div>
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
	    <label for="medical_license_number">Medical License Number</label>
	    <input type="text" class="form-control" name="medical_license_number" required value="<?php echo $medical_license_number; ?>">
	</div>
	<div class="form-group">
	    <label for="hospital_name">Hospital Name</label>
	    <input type="text" class="form-control" name="hospital_name" required value="<?php echo $hospital_name; ?>">
	</div>
	<div class="form-group">
	    <label for="specialization">Specialization</label>
	    <input type="text" class="form-control" name="specialization" required value="<?php echo $specialization; ?>">
	</div>
	<div class="form-group">
	    <label for="password">Password</label>
	    <input type="password" class="form-control" name="password" required>
	</div>
	<div class="form-group">
	    <label for="image_upload">Upload Image</label>
	    <input type="file" class="form-control-file" name="image_upload" accept="image/*" required>
	</div>

	<button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php
include('../footer.php');
?>
