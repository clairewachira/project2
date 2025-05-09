<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$first_name = $_POST["first_name"];
	$surname = $_POST["surname"];
	$gender = $_POST["gender"];
	$email_address = $_POST["email_address"];
	$phone_number = $_POST["phone_number"];
	$password = $_POST["password"];

	if ($_FILES["image_upload"]["error"] === 0) {
		$image_name = $_FILES["image_upload"]["name"];
		$image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

		$unique_image_name = uniqid('admin_') . '.' . $image_extension;

		$target_directory = '../static/images/administrators/';

		$target_file = $target_directory . $unique_image_name;

		$is_image = getimagesize($_FILES["image_upload"]["tmp_name"]);
		if ($is_image) {
			if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $target_file)) {
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);

				$query = "INSERT INTO administrator (first_name, surname, gender, email_address, phone_number, password_hash, image_url)
					VALUES ('$first_name', '$surname', '$gender', '$email_address', '$phone_number', '$hashed_password', '$unique_image_name')";

				if ($mysqli->query($query) === TRUE) {
					$_SESSION["success_message"] = "Registration successful! You can now log in.";
					header("Location: login.php");
					exit();
				} else {
					$_SESSION["error_message"] = "Registration failed. Please try again later.";
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
	<h2>Administrator Registration</h2>
<?php
if (isset($_SESSION["error_message"])) {
	echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
	unset($_SESSION["error_message"]);
}
?>

	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
	    <div class="form-group">
		<label for="first_name">First Name</label>
		<input type="text" class="form-control" name="first_name" required>
	    </div>
	    <div class="form-group">
		<label for="surname">Surname</label>
		<input type="text" class="form-control" name="surname" required>
	    </div>
	    <div class="form-group">
		<label for="gender">Gender</label>
		<select class="form-control" name="gender">
		    <option value="Male">Male</option>
		    <option value="Female">Female</option>
		    <option value="Other">Other</option>
		</select>
	    </div>
	    <div class="form-group">
		<label for="email_address">Email Address</label>
		<input type="email" class="form-control" name="email_address" required>
	    </div>
	    <div class="form-group">
		<label for="phone_number">Phone Number</label>
		<input type="text" class="form-control" name="phone_number">
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
</body>
</html>
