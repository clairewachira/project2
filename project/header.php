<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Drug Dispensing System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
		</li>
<?php
if (isset($_SESSION['user_type']))
{
	$selected_role = $_SESSION['user_type'];
	$redirect_page = '../profiles/' . $selected_role . '_profile.php?' . $selected_role . '_id=' . $_SESSION['user_id'];
                echo '<li class="nav-item">';
	echo '<a class="nav-link" href="' . $redirect_page . '">Profile</a>';
                echo '</li><li class="nav-item">';
	echo '<a class="nav-link" href="../login/logout.php">Logout</a>';
	echo "</li>";
} 
else 
{
                    echo '<a class="nav-link" href="../login/login.php">Login</a>';
}
?>
		</li>
            </ul>
        </div>
    </nav>
