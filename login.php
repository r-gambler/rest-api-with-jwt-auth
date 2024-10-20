<?php
//error_reporting(E_ALL); ini_set('display_errors', 1);
include_once 'db.php';
include_once 'pokemon.php';

session_start();

require __DIR__ . '/vendor/autoload.php'; // Include the Composer autoloader

use Firebase\JWT\JWT;

// Secret key for JWT
$secret_key = "markskey"; // Change this to your actual secret key

// Create a new instance of the Database class and establish the connection
$db = new Database();
$connection = $db->connect();

try {
    // Create a new instance of the Pokemon class and pass the mysqli connection
    $pokemon = new Pokemon($connection);

    $request_method = $_SERVER["REQUEST_METHOD"];
    $uri = $_SERVER["REQUEST_URI"];

    switch ($request_method) {
        case 'POST':
            //$data = json_decode(file_get_contents("php://input"));
            $email1 = $_REQUEST["email"];
            $password1 = $_REQUEST["password"];
            // Check if email and password are provided
            if (!empty($email1) && !empty($password1)) {
                $email = $email1;
                $password = $password1;

                // Query the database to fetch user details
                $query = "SELECT * FROM pokemonusers WHERE email = ? LIMIT 1";
                $stmt = $connection->prepare($query);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    // Verify password
                    if ($password === $user['password']) {
                        // Generate JWT token
                        $token = generateToken($user['id'], $user['email']);
                        http_response_code(200);
                        echo json_encode(array("message" => "Login successful", "token" => $token));
                    } else {
                        http_response_code(401);
                        echo json_encode(array("message" => "Invalid email or password1"));
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid email or password"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Email and password are required"));
            }
            break;

        // Add other cases for handling different HTTP methods and endpoints

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed."));
            break;
    }

} catch (Exception $e) {
    // JWT token is invalid or expired
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized1"));
    exit();
}

// Function to generate JWT token
function generateToken($user_id, $email) {
    global $secret_key;
    $payload = array(
        "user_id" => $user_id,
        "email" => $email,
        "exp" => time() + (60 * 60) // Token expiration time (1 hour)
    );
    $token = JWT::encode($payload, $secret_key);
    $_SESSION['token'] = $token;
    return $token;
}
?>
