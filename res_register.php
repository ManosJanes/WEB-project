<?php
include 'access.php';

if (isset($_SESSION['user_id'])) {
    header("Location: demo.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phone'];
    $newuname = $_POST['newuname'];
    $newpsw = $_POST['newpsw'];

    if (empty($name) || empty($surname) || empty($phone) || empty($newuname) || empty($newpsw)) {
        echo "All fields are required.";
    } else {

        // Insert user into the users table with plain text password
        $sql = "INSERT INTO users (name, surname, phone, username, password) VALUES ('$name', '$surname', '$phone', '$newuname', '$newpsw')";
        if (mysqli_query($con, $sql)) {
            // Get the last inserted ID
            $last_inserted_id = mysqli_insert_id($con);

            $resql = "INSERT INTO rescuer (res_id) VALUES ('$last_inserted_id')";
            if (mysqli_query($con, $resql)) {
                // Redirect to register_map.php after successful registration
                header("Location: admin.php");
                exit();
            } else {
                echo "Error inserting into rescuer table: " . mysqli_error($con);
            }
        } else {
            echo "Error inserting into users table: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Sign Up</title>
</head>
<style>
    .signup-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 300px;
        text-align: center;
        margin-top: 20px;
    }

    .signup-container label,
    .signup-container input {
        margin-top: 15px;
    }
</style>

<body>

    <form action="res_register.php" method="post">
        <div class="signup-container">
            <h1>Sign Up Rescuer</h1>

            <label for="name"><b>Name</b></label>
            <input type="text" placeholder="Enter Name" name="name" required>

            <label for="surname"><b>Surname</b></label>
            <input type="text" placeholder="Enter Surname" name="surname" required>

            <label for="phone"><b>Phone</b></label>
            <input type="phone" placeholder="Enter Phone" name="phone" required>

            <label for="newuname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="newuname" required>

            <label for="newpsw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="newpsw" required>

            <button type="submit">Sign Up Rescuer</button>

        </div>

        <div class="login"><a href="admin.php">GO BACK</a></div>

    </form>

</body>

</html>
