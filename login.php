<?php
function secure_session_start() {
    $session_name = 'sec_session_id';
    $secure = true; // Set to true if using https
    $httponly = true;

    ini_set('session.use_only_cookies', 1);
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly
    );

    session_name($session_name);
    session_start();
    session_regenerate_id(true);
}

// Call the function before any session-related operations
secure_session_start();

$error_msg = "";
$display_login_form = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error_msg = "Username and password cannot be empty.";
    } else {
        $conn = new mysqli("localhost", "root", "", "assignment1");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $username = $conn->real_escape_string(htmlspecialchars($_POST['username']));
        $password = $_POST['password'];

        $check_query = "SELECT * FROM users WHERE username='$username'";
        $check_result = $conn->query($check_query);

        if ($check_result && $check_result->num_rows == 1) {
            $user_data = $check_result->fetch_assoc();
            $salt = $user_data['salt'];
            $password_hashed = hash('sha256', $password . $salt);
            $stored_password = $user_data['password'];

            if ($password_hashed == $stored_password) {
                // Prevent Session Fixation by regenerating session ID after successful login
                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                // Debugging line to verify session value
                error_log("Login successful, session username: " . $_SESSION['username']);
                header("Location: order.php");
                exit();
            } else {
                $error_msg = "Invalid password";
            }
        } else {
            $error_msg = "Invalid username";
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        form {
            width: 300px;
            margin: auto;
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
        button {
            background-color: lightgreen;
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
        <h2>Login</h2>
        <p class="error-msg"><?php echo $error_msg; ?></p>
        <?php if ($display_login_form): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                Username: <input type="text" name="username" value="<?php if(isset($_POST['username'])) echo htmlspecialchars($_POST['username']); ?>"><br><br>
                Password: <input type="password" name="password"><br><br>
                <input type="submit" value="Login">
                <button type="button" onclick="location.href='home.html'">Go Back</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
