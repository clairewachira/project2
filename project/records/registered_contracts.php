<?php
session_start();
include('../header.php');

require_once('../credentials.php');

$mysqli = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$query = "SELECT contract_id, pharmacy_id, pharmaceutical_id FROM contract";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    echo '<div class="container mt-5">';
    echo '<h2>Registered Contracts</h2>';
    echo '<table class="table table-bordered">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Contract ID</th>';
    echo '<th>Pharmacy</th>';
    echo '<th>Pharmaceutical</th>';
    echo '<th>Contract Profile</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($row = $result->fetch_assoc()) {
        $contract_id = $row['contract_id'];
        $pharmacy_id = $row['pharmacy_id'];
        $pharmaceutical_id = $row['pharmaceutical_id'];

        $pharmacy_query = "SELECT name FROM pharmacy WHERE pharmacy_id = ?";
        $pharmacy_stmt = $mysqli->prepare($pharmacy_query);
        $pharmacy_stmt->bind_param('i', $pharmacy_id);
        $pharmacy_stmt->execute();
        $pharmacy_result = $pharmacy_stmt->get_result();
        $pharmacy_row = $pharmacy_result->fetch_assoc();

        $pharmaceutical_query = "SELECT name FROM pharmaceutical WHERE pharmaceutical_id = ?";
        $pharmaceutical_stmt = $mysqli->prepare($pharmaceutical_query);
        $pharmaceutical_stmt->bind_param('i', $pharmaceutical_id);
        $pharmaceutical_stmt->execute();
        $pharmaceutical_result = $pharmaceutical_stmt->get_result();
        $pharmaceutical_row = $pharmaceutical_result->fetch_assoc();

        echo '<tr>';
        echo '<td>' . $contract_id . '</td>';
        echo '<td>' . $pharmacy_row['name'] . '</td>';
        echo '<td>' . $pharmaceutical_row['name'] . '</td>';
        echo '<td>';
        echo '<a href="../profiles/contract_profile.php?contract_id=' . $contract_id . '">Contract Profile</a> | ';
        echo '<a href="../profiles/pharmacy_profile.php?pharmacy_id=' . $pharmacy_id . '">Pharmacy Profile</a> | ';
        echo '<a href="../profiles/pharmaceutical_profile.php?pharmaceutical_id=' . $pharmaceutical_id . '">Pharmaceutical Profile</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="container mt-5">';
    echo '<p>No contracts found.</p>';
    echo '</div>';
}

$mysqli->close();
?>

<?php
include('../footer.php');
?>
