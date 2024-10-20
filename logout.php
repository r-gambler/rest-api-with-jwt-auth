<?php
// Include required files and libraries
include_once 'db.php';
include_once 'pokemon.php';
require __DIR__ . '/vendor/autoload.php'; // Include the Composer autoloader
use Firebase\JWT\JWT;

// Secret key for JWT
$secret_key = "markskey";

// Function to validate JWT token and check if it's blacklisted
function validateToken() {
    global $secret_key;
    $token = null;
    $authHeader = $_SERVER['HTTP_BEARER'];
    if (isset($authHeader)) {
        //$arr = explode(" ", $authHeader);
        $token = $authHeader;
    }

    if ($token) {
        try {
            // Check if token is blacklisted
            if (isTokenBlacklisted($token)) {
                return false; // Token is blacklisted
            }

            // Validate the token
            $decoded = JWT::decode($token, $secret_key, array('HS256'));

            // Check token expiration
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return false; // Token expired
            }
            return true; // Token valid
        } catch (Exception $e) {
            return false; // Token invalid
        }
    } else {
        return false; // Token not provided
    }
}

// Function to add a token to the blacklist upon logout
function addToBlacklist($token) {
    // Implement your logic to store the token in a blacklist (e.g., database)
    // Here, we'll just store it in a session variable for demonstration purposes
    session_start();
    $_SESSION['blacklist'][$token] = true;
}

// Function to check if a token is blacklisted
function isTokenBlacklisted($token) {
    // Implement your logic to check if the token is blacklisted (e.g., check database)
    // Here, we'll just check if it exists in a session variable for demonstration purposes
    session_start();
    return isset($_SESSION['blacklist'][$token]);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is authenticated
    if (validateToken()) {
        // Perform logout actions (invalidate token and add to blacklist)
        $token = $_SERVER['HTTP_BEARER'];
        addToBlacklist($token);

        // Respond with a success message
        http_response_code(200);
        echo json_encode(array("message" => "Logout successful"));
    } else {
        // If the user is not authenticated, respond with an error
        http_response_code(401);
        echo json_encode(array("message" => "Unauthorized"));
    }
} else {
    // If the request method is not POST, respond with a method not allowed error
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}
?>
