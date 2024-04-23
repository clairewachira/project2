<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$errors = array();

function sanitize_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function connect_to_database() {
	global $database_host, $database_user, $database_password, $database_name;
	$conn = new mysqli($database_host, $database_user, $database_password, $database_name);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}

function add_patient($conn, $first_name, $surname, $gender, $email_address, $phone_number, $date_of_birth, $social_security_number, $password_hash, $image_url) {
	$stmt = $conn->prepare("INSERT INTO patient (first_name, surname, gender, email_address, phone_number, date_of_birth, social_security_number, password_hash, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("sssssssss", $first_name, $surname, $gender, $email_address, $phone_number, $date_of_birth, $social_security_number, $password_hash, $image_url);

	if ($stmt->execute()) {
		return true;
	} else {
		return false;
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$first_name = sanitize_input($_POST["first_name"]);
	$surname = sanitize_input($_POST["surname"]);
	$gender = sanitize_input($_POST["gender"]);
	$email_address = sanitize_input($_POST["email_address"]);
	$phone_number = sanitize_input($_POST["phone_number"]);
	$date_of_birth = sanitize_input($_POST["date_of_birth"]);
	$social_security_number = sanitize_input($_POST["social_security_number"]);
	$password_hash = password_hash(sanitize_input($_POST["password"]), PASSWORD_DEFAULT);
	$image_url = '';

	if (!empty($_FILES['image']['name'])) {
		$target_dir = "../static/images/patients/";
		$image_name = basename($_FILES["image"]["name"]);
		$image_path = $target_dir . $image_name;

		$imageFileType = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
		$allowed_extensions = array("jpg", "jpeg", "png", "gif");
		if (!in_array($imageFileType, $allowed_extensions)) {
			$errors[] = "Only JPG, JPEG, PNG, and GIF images are allowed.";
		}

		if (file_exists($image_path)) {
			$errors[] = "The file already exists.";
		}

		if (empty($errors)) {
			if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
				$image_url = "static/images/patients/" . $image_name;
			} else {
				$errors[] = "Failed to upload the image. Please try again later.";
			}
		}
	}

	if (empty($first_name)) {
		$errors[] = "First name is required.";
	}

	if (empty($surname)) {
		$errors[] = "Surname is required.";
	}

	if (empty($email_address)) {
		$errors[] = "Email address is required.";
	} elseif (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Invalid email address format.";
	}

	if (empty($errors)) {
		$conn = connect_to_database();
		if (add_patient($conn, $first_name, $surname, $gender, $email_address, $phone_number, $date_of_birth, $social_security_number, $password_hash, $image_url)) {
			header("Location: ../records/registered_patients.php");
			exit();
		} else {
			$errors[] = "Failed to add patient. Please try again later.";
		}
		$conn->close();
	}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Patient</title>
    <!-- Add Bootstrap CSS link here -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/form.css">
</head>
<body>
    <div class="container">
	<h2>Register Patient</h2>
	<?php if (!empty($errors)): ?>
	<div class="alert alert-danger" role="alert">
	    <ul>
		<?php foreach ($errors as $error): ?>
		<li><?php echo $error; ?></li>
		<?php endforeach; ?>
	    </ul>
	</div>
	<?php endif; ?>

	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
	    <div class="form-group">
		<label for="first_name">First Name:</label>
		<input type="text" class="form-control" id="first_name" name="first_name" required>
	    </div>
	    <div class="form-group">
		<label for="surname">Surname:</label>
		<input type="text" class="form-control" id="surname" name="surname" required>
	    </div>
	    <div class="form-group">
		<label for="gender">Gender:</label>
		<select class="form-control" id="gender" name="gender">
		    <option value="Male">Male</option>
		    <option value="Female">Female</option>
		    <option value="Other">Other</option>
		</select>
	    </div>
	    <div class="form-group">
		<label for="email_address">Email Address:</label>
		<input type="email" class="form-control" id="email_address" name="email_address" required>
	    </div>
	    <div class="form-group">
		<label for="phone_number">Phone Number:</label>
		<input type="tel" class="form-control" id="phone_number" name="phone_number">
	    </div>
	    <div class="form-group">
		<label for="date_of_birth">Date of Birth:</label>
		<input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
	    </div>
	    <div class="form-group">
		<label for="social_security_number">Social Security Number:</label>
		<input type="text" class="form-control" id="social_security_number" name="social_security_number">
	    </div>
	    <div class="form-group">
		<label for="password">Password:</label>
		<input type="password" class="form-control" id="password" name="password" required>
	    </div>
	    <div class="form-group">
		<label for="image">Profile Image:</label>
		<input type="file" class="form-control-file" id="image" name="image">
	    </div>
	    <button type="submit" class="btn btn-primary">Register</button>
	</form>
    </div>
</body>
</html>
