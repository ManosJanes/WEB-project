<?php
include 'access.php';

$username = $_POST['uname'];
$password = $_POST['psw'];

// To prevent SQL injection
$username = stripcslashes($username);
$password = stripcslashes($password);
$username = mysqli_real_escape_string($con, $username);
$password = mysqli_real_escape_string($con, $password);

// Check for user in any of the tables
$sql = "SELECT u.id, a.adm_id, r.res_id, c.cit_id
        FROM users u
        LEFT JOIN admin a ON u.id = a.adm_id
        LEFT JOIN rescuer r ON u.id = r.res_id
        LEFT JOIN citizen c ON u.id = c.cit_id
        WHERE u.username = '$username' AND u.password = '$password'";

$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
$count = mysqli_num_rows($result);

// Check if the user is an admin, rescuer, or citizen based on their ID
if ($count == 1) {
    // User found in any of the tables
    session_start();
    $_SESSION['user_id'] = $row['id'];
    if (!empty($row['adm_id'])) {
        $_SESSION['user_role'] = 'admin';
        header("Location: admin.php");
    } elseif (!empty($row['res_id'])) {
        $_SESSION['user_role'] = 'rescuer';
        header("Location: rescuer.php");
    } elseif (!empty($row['cit_id'])) {
        $_SESSION['user_role'] = 'citizen';
        header("Location: citizen.php");
    }
} else {
    header("Location: demo.php?error=1");
}
?>
