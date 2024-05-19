<?php
function secure_session_start() {
    $session_name = 'sec_session_id';
    $secure = false; // Set to true if using https
    $httponly = true;

    ini_set('session.use_only_cookies', 1);
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

    session_name($session_name);
    session_start();
    session_regenerate_id(true);
}

secure_session_start();

if (!isset($_SESSION['username'])) {
    error_log("Access denied. Session username is not set.");
    header("Location: login.php?error=You need to log in to access this page");
    exit();
}

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['pizza_type']) && !empty($_POST['quantity'])) {
        $pizza_type = htmlspecialchars($_POST['pizza_type']);
        $quantity = (int)$_POST['quantity'];

        $conn = new mysqli("localhost", "root", "", "assignment1");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $username = $_SESSION['username'];
        $order_query = "INSERT INTO orders (username, pizza_type, quantity) VALUES ('$username', '$pizza_type', $quantity)";
        if ($conn->query($order_query) === TRUE) {
            $success_msg = "Order placed successfully!";
        } else {
            $error_msg = "Error placing order: " . $conn->error;
        }

        $conn->close();
    } else {
        $error_msg = "Please select a pizza type and quantity.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Pizza</title>
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
        input, select, button {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: lightgreen;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Order Pizza</h2>
        <?php if (!empty($error_msg)): ?>
            <p class="error-msg"><?php echo $error_msg; ?></p>
        <?php endif; ?>
        <?php if (!empty($success_msg)): ?>
            <p class="success-msg"><?php echo $success_msg; ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            Pizza Type:
            <select name="pizza_type" required>
                <option value="">Select Pizza</option>
                <option value="Margherita">Margherita</option>
                <option value="Pepperoni">Pepperoni</option>
                <option value="Veggie">Veggie</option>
            </select><br>
            Quantity:
            <input type="number" name="quantity" min="1" required><br>
            <button type="submit">Place Order</button>
            <button type="button" onclick="location.href='home.html'">Go Back</button>
        </form>
    </div>
</body>
</html>
