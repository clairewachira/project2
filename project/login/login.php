<?php
require_once('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'];
	$password = $_POST['password'];
	$selected_role = $_POST['role']; // Added role selection


	$hashed_password = password_hash($password, PASSWORD_DEFAULT);

	$table_name = '';
	$redirect_page = '';

	switch ($selected_role) {
	case 'administrator':
		$table_name = 'administrator';
		$redirect_page = 'administrator_profile.php';
		break;
	case 'pharmacist':
		$table_name = 'pharmacist';
		$redirect_page = 'pharmacist_profile.php';
		break;
	case 'supervisor':
		$table_name = 'supervisor';
		$redirect_page = 'supervisor_profile.php';
		break;
	case 'doctor':
		$table_name = 'doctor';
		$redirect_page = 'doctor_profile.php';
		break;
	case 'patient':
		$table_name = 'patient';
		$redirect_page = 'patient_profile.php';
		break;
	default:
		$error_message = "Invalid role selected.";
		break;
	}

	if (!empty($table_name)) {
		$query = "SELECT * FROM $table_name WHERE email_address = ?";
		$stmt = $mysqli->prepare($query);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 1) {
			$user = $result->fetch_assoc();

			if (password_verify($password, $user['password_hash'])) {
				$_SESSION['user_id'] = $user[$table_name . '_id'];
				$_SESSION['user_type'] = $selected_role;
				header('Location: ../profiles/' . $redirect_page);
				exit;
			} else {
				$error_message = "Incorrect password.";
			}
		} else {
			$error_message = "User not found.";
		}

		$stmt->close();
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
	<div class="row justify-content-center">
	    <div class="col-md-6">
		<div class="card">
		    <div class="card-header">Login</div>
		    <div class="card-body">
			<?php if (isset($error_message)) { ?>
			    <div class="alert alert-danger"><?php echo $error_message; ?></div>
			<?php } ?>
			<form method="POST" action="">
			    <div class="form-group">
				<label for="role">Select Role:</label>
				<select class="form-control" id="role" name="role" required>
				    <option value="administrator">Administrator</option>
				    <option value="pharmacist">Pharmacist</option>
				    <option value="supervisor">Supervisor</option>
				    <option value="doctor">Doctor</option>
				    <option value="patient">Patient</option>
				</select>
			    </div>
			    <div class="form-group">
				<label for="email">Email:</label>
				<input type="text" class="form-control" id="email" name="email" required>
			    </div>
			    <div class="form-group">
				<label for="password">Password:</label>
				<input type="password" class="form-control" id="password" name="password" required>
			    </div>
			    <button type="submit" class="btn btn-primary">Login</button>
			</form>
		    </div>
		</div>
	    </div>
	</div>
    </div>
</body>
</html>
