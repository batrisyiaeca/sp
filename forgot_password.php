<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: lightpink; /* Set background color to light pink */
        }
        .container {
            text-align: center;
        }
        form {
            width: 300px;
            margin: auto; /* Center the form horizontally */
        }
        input[type="text"],
        input[type="submit"] {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        p.error-msg {
            color: red;
        }
        p.success-msg {
            color: green;
        }
        button {
            background-color: lightgreen; /* Set button color to light green */
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>

        <?php
        // Initialize variables
        $error_msg = "";
        $success_msg = "";

        // Check if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Connect to MySQL
            $conn = new mysqli("localhost", "root", "", "assignment1");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Sanitize input
            $username = $conn->real_escape_string($_POST['username']);

            // Check if username exists
            $check_query = "SELECT * FROM users WHERE username='$username'";
            $check_result = $conn->query($check_query);

            if ($check_result->num_rows == 1) {
                // Username exists, generate new password
                $new_password = generateRandomPassword(); // Function to generate random password
                $hashed_password = hash('sha256', $new_password); // Hash the new password

                // Update user's temporary password and salt in the database
                $salt = generateRandomSalt(); // Function to generate random salt
                $hashed_password = hash('sha256', $hashed_password . $salt); // Hash password with salt

                $update_query = "UPDATE users SET temp_password='$hashed_password', salt='$salt' WHERE username='$username'";
                if ($conn->query($update_query) === TRUE) {
                    // Temporary password updated successfully
                    $success_msg = "Your temporary password has been sent to your email address.";
                    // Display popup message
                    echo '<script>alert("Your temporary password is: ' . $new_password . '"); window.location.href = "login.php";</script>';
                } else {
                    $error_msg = "Error updating temporary password: " . $conn->error;
                }
            } else {
                // Username doesn't exist
                $error_msg = "Username not found.";
            }

            $conn->close();
        }

        // Function to generate random password
        function generateRandomPassword($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $password;
        }

        // Function to generate random salt
        function generateRandomSalt($length = 16) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $salt = '';
            for ($i = 0; $i < $length; $i++) {
                $salt .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $salt;
        }
        ?>
        
        <!-- Display error or success message -->
        <?php if (!empty($error_msg)): ?>
            <p class="error-msg"><?php echo $error_msg; ?></p>
        <?php endif; ?>
        <?php if (!empty($success_msg)): ?>
            <p class="success-msg"><?php echo $success_msg; ?></p>
        <?php endif; ?>

        <!-- Display forgot password form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            Enter your username: <input type="text" name="username" required><br><br>
            <input type="submit" value="Reset Password" style="background-color: lightgreen;">
        </form>
    </div>
</body>
</html>
