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
        header("Location: res_register.php?error=emptyfields&name=".$name."&surname=".$surname."&phone=".$phone."&newuname=".$newuname);
        exit();
    } else {

        // Insert user into the users table with plain text password
        $sql = "INSERT INTO users (name, surname, phone, username, password) VALUES ('$name', '$surname', '$phone', '$newuname', '$newpsw')";
        if (mysqli_query($con, $sql)) {

            // Get the last inserted ID
            $last_inserted_id = mysqli_insert_id($con);

            $resql = "INSERT INTO rescuer (res_id) VALUES (?)";
            if (mysqli_query($con, $resql)) {
                // Set the user ID in the session

                $con->close();

                header("Location: admin.php");
                exit();
            } else {
                echo "Error inserting into rescuer table: " . mysqli_error($con);
            }
        } else {
            echo "Error inserting into users table: " . mysqli_error($con);
        }
    }
} else {
    header("Location: res_register.php");
    exit();
}
?>
