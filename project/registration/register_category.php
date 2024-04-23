<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$name = $_POST["name"];
	$description = $_POST["description"];

	$query = "INSERT INTO category (name, description) VALUES (?, ?)";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("ss", $name, $description);
	if ($stmt->execute()) {
		$_SESSION["success_message"] = "Category registration successful!";
		header("Location: register_category.php"); // Redirect to the same page
		exit();
	} else {
		$_SESSION["error_message"] = "Category registration failed. Please try again later.";
		echo $stmt->error;
	}
	$stmt->close();
}

$mysqli->close();
?>

<div class="container mt-5">
    <h2>Category Registration</h2>
<?php
if (isset($_SESSION["error_message"])) {
	echo '<div class="alert alert-danger">' . $_SESSION["error_message"] . '</div>';
	unset($_SESSION["error_message"]);
}
if (isset($_SESSION["success_message"])) {
	echo '<div class="alert alert-success">' . $_SESSION["success_message"] . '</div>';
	unset($_SESSION["success_message"]);
}
?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
	<div class="form-group">
	    <label for="name">Category Name</label>
	    <input type="text" class="form-control" name="name" required>
	</div>
	<div class="form-group">
	    <label for="description">Description</label>
	    <textarea class="form-control" name="description"></textarea>
	</div>
	<button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php
include('../footer.php');
?>
