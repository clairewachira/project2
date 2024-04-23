<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


$name = $location = $email_address = $phone_number = $password = $registration_success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$name = $_POST["name"];
	$location = $_POST["location"];
	$email_address = $_POST["email_address"];
	$phone_number = $_POST["phone_number"];
	$password = $_POST["password"];

	$hashed_password = password_hash($password, PASSWORD_DEFAULT);

	$query = "INSERT INTO pharmaceutical (name, location, email_address, phone_number, password_hash)
		VALUES ('$name', '$location', '$email_address', '$phone_number', '$hashed_password')";

	if ($mysqli->query($query) === TRUE) {
		$registration_success = true;
	} else {
		$_SESSION["error_message"] = "Pharmaceutical registration failed. Please try again later.";
	}
}

$mysqli->close();
?>

<div class="container mt-5">
    <h2>Pharmaceutical Registration</h2>
<?php
if (isset($_SESSION["error_message"])) {
	echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
	unset($_SESSION["error_message"]);
}

if ($registration_success) {
	echo '<div class="alert alert-success">Pharmaceutical registration successful!</div>';
}
?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
	<!-- Add form fields for pharmaceutical registration -->
	<div class="form-group">
	    <label for="name">Name</label>
	    <input type="text" class="form-control" name="name" required value="<?php echo $name; ?>">
	</div>
	<div class="form-group">
	    <label for="location">Location</label>
	    <input type="text" class="form-control" name="location" required value="<?php echo $location; ?>">
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
	<button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php
include('../footer.php');
?>
