<?php

include 'access.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phone'];
    $newuname = $_POST['newuname'];
    $newpsw = $_POST['newpsw'];

    if (empty($name) || empty($surname) || empty($phone) || empty($newuname) || empty($newpsw)) {
        header("Location: signup.php?error=emptyfields&name=".$name."&surname=".$surname."&phone=".$phone."&newuname=".$newuname);
        exit();
    } else {

        // Insert user into the users table with plain text password
        $sql = "INSERT INTO users (name, surname, phone, username, password) VALUES ('$name', '$surname', '$phone', '$newuname', '$newpsw')";
        if (mysqli_query($con, $sql)) {

            // Get the last inserted ID
            $last_inserted_id = mysqli_insert_id($con);

            // Insert corresponding record into the citizen table
            $citsql = "INSERT INTO citizen (cit_id) VALUES ('$last_inserted_id')";
            if (mysqli_query($con, $citsql)) {
                // Set the user ID in the session
                $_SESSION['user_id'] = $last_inserted_id;

                $con->close();

                header("Location: register_map.php");
                exit();
            } else {
                echo "Error inserting into citizen table: " . mysqli_error($con);
            }
        } else {
            echo "Error inserting into users table: " . mysqli_error($con);
        }
    }
} else {
    header("Location: signup.php");
    exit();
}
?>
