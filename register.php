<?php
// Function to generate a random salt
function generateSalt() {
    $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $salt = '';
    for ($i = 0; $i < 16; $i++) {
        $salt .= $charset[rand(0, strlen($charset) - 1)];
    }
    return $salt;
}

// Initialize error message and registration form display
$error_msg = "";
$display_registration_form = true;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are not empty
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_msg = "Username and password cannot be empty.";
    } else {
        // Securely start session
        secure_session_start();

        // Connect to MySQL
        $conn = new mysqli("localhost", "root", "", "assignment1");

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Sanitize input
        $username = $conn->real_escape_string(htmlspecialchars($_POST['username']));
        $password = $_POST['password']; // Don't hash password yet

        // Generate salt
        $salt = generateSalt();
        // Combine password and salt
        $password = hash('sha256', $password . $salt);

        // Check if username exists
        $check_query = "SELECT * FROM users WHERE username='$username'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows > 0) {
            // Username already exists, set error message
            $error_msg = "Username already exists. Please choose a different username.";
        } else {
            // Validate password
            if (preg_match("/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $_POST['password'])) {
                // Password meets criteria, proceed with registration
                $sql = "INSERT INTO users (username, password, salt) VALUES ('$username', '$password', '$salt')";
                if ($conn->query($sql) === TRUE) {
                    echo "Registration successful";
                    echo '<br><button onclick="location.href=\'home.html\'">Go to Home</button>'; // Button to go to home.html
                    // Set to not display registration form
                    $display_registration_form = false;
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                $error_msg = "Password must be at least 8 characters long and contain at least one letter, one number, and one symbol.";
            }
        }

        $conn->close();
    }
}

// Function to securely start session
function secure_session_start() {
    $session_name = 'sec_session_id';
    $secure = true; // Set to true if using https
    $httponly = true; // This stops JavaScript being able to access the session id

    ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

    session_name($session_name);
    session_start();
    session_regenerate_id(true); // Regenerate the session, delete the old one.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: lightpink;
        }
        .container {
            text-align: center;
        }
        button {
            background-color: lightgreen;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }
        input[type="text"],
        input[type="password"],
        input[type="submit"] {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        p.error-msg {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <!-- Display error message -->
        <p class="error-msg"><?php echo $error_msg; ?></p>
        
        <!-- Display registration form if needed -->
        <?php if ($display_registration_form): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return validateForm()">
                Username: <input type="text" name="username" value="<?php if(isset($_POST['username'])) echo htmlspecialchars($_POST['username']); ?>"><br><br>
                Password: <input type="password" name="password"><br><br>
                <input type="submit" value="Register" style="background-color: lightgreen;">
                <button type="button" onclick="location.href='home.html'">Go Back</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function validateForm() {
            var username = document.getElementsByName("username")[0].value;
            var password = document.getElementsByName("password")[0].value;

            // Password pattern: minimum 8 characters, at least one letter, one number, and one symbol
            var passwordPattern = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (username.trim() == "" || password.trim() == "") {
                alert("Username and password cannot be empty.");
                return false;
            } else if (!passwordPattern.test(password)) {
                alert("Password must be at least 8 characters long and contain at least one letter, one number, and one symbol.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
