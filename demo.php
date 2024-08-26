<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login to our system</title>

    <style>
        .signup-container {
            display: none;
        }
    </style>
</head>

<body>

    <form id="loginForm" action="login.php" method="post">
        <div class="container">
            <h1>Login form</h1>

            <label for="uname"><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="uname" required>

            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="psw" required>

            <?php
            if (isset($_GET['error']) == true) {
                echo '<font color="#FF0000"><p align="center">Invalid username/password</p></font>';
            }
            if(isset($_GET['block'])==true){
                echo '<font colour="#FF0000"><p align="center">You need to connect to have the access</p></font>';
             }
            ?>

            <button type="submit">Login</button>
        </div>

        <div class="signup"><a href="signup.php">You don't have an account? Sign up!</a></div>
    </form>

    <form id="signupForm" action="signup.php" method="post" style="display: none;">
        <div class="signup-container">
            <h1>Sign Up</h1>

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

            <button type="submit">Sign Up</button>
        </div>
        <div class="login"><a href="demo.php">You already have an account? Log in!</a></div>

    </form>
 
</body>
</html>