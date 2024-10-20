<?php
//error_reporting(E_ALL); ini_set('display_errors', 1);
include_once 'db.php';
include_once 'pokemon.php';

session_start();

require __DIR__ . '/vendor/autoload.php'; // Include the Composer autoloader

use Firebase\JWT\JWT;

// Secret key for JWT
$secret_key = "markskey";

// Create a new instance of the Database class and establish the connection
$db = new Database();
$connection = $db->connect();

try {
    // Create a new instance of the Pokemon class and pass the mysqli connection
    $pokemon = new Pokemon($connection);

    $request_method = $_SERVER["REQUEST_METHOD"];
    $uri = $_SERVER["REQUEST_URI"];

    switch ($request_method) {
        case 'GET':
            // Validate JWT token
            if (!validateToken()) {
                http_response_code(401);
                echo json_encode(array("message" => "Unauthorized"));
                exit();
            }

            if (isset($_GET["pid"])) {
                $pid = $_GET["pid"];
                $result = $pokemon->readOne($pid);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    http_response_code(200);
                    echo json_encode($row);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Pokemon not found."));
                }
            } else {
                $result = $pokemon->read();
                if ($result->num_rows > 0) {
                    $pokemons_arr = array();
                    while ($row = $result->fetch_assoc()) {
                        $pokemons_arr[] = $row;
                    }
                    http_response_code(200);
                    echo json_encode($pokemons_arr);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "No pokemons found."));
                }
            }
            break;
        case 'POST':
            // Validate JWT token
            if (!validateToken()) {
                http_response_code(401);
                echo json_encode(array("message" => "Unauthorized"));
                exit();
            }

            //$data = json_decode(file_get_contents("php://input"));
            $pname = $_REQUEST["pokemonname"];
            $ptype = $_REQUEST["pokemontype"];
            $ploc = $_REQUEST["pokemonlocation"];
            if (!empty($pname) && !empty($ptype) && !empty($ploc)) {
                $pokemonname = $pname;
                $pokemontype = $ptype;
                $pokemonlocation = $ploc;
                $pokemon->create($pokemonname, $pokemontype, $pokemonlocation);
                http_response_code(201);
                echo json_encode(array("message" => "Pokemon was created."));
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to create pokemon. Data is incomplete."));
            }
            break;
        case 'PUT':
            // Validate JWT token
            if (!validateToken()) {
                http_response_code(401);
                echo json_encode(array("message" => "Unauthorized"));
                exit();
            }
            //parse_str(file_get_contents("php://input"), $put_vars);
            $pid = $_REQUEST["pid"];
            $pname = $_REQUEST["pokemonname"];
            $ptype = $_REQUEST["pokemontype"];
            $ploc = $_REQUEST["pokemonlocation"];
            if (isset($pid)) {
                //$pid = $put_vars["pid"];
                //$data = json_decode(file_get_contents("php://input"));
                if (!empty($pname) && !empty($ptype) && !empty($ploc)) {
                    $pokemonname = $pname;
                    $pokemontype = $ptype;
                    $pokemonlocation = $ploc;
                    $pokemon->update($pid, $pokemonname, $pokemontype, $pokemonlocation);
                    http_response_code(200);
                    echo json_encode(array("message" => "Pokemon was updated."));
                } else {
                    http_response_code(400);
                    echo json_encode(array("message" => "Unable to update pokemon. Data is incomplete."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "ID is required."));
            }
            break;
        case 'DELETE':
            // Validate JWT token
            if (!validateToken()) {
                http_response_code(401);
                echo json_encode(array("message" => "Unauthorized"));
                exit();
            }
            //parse_str(file_get_contents("php://input"), $delete_vars);
            // Get the parameters from the request URL
            $pid = $_REQUEST["pid"];
            if (isset($pid)) {
                //$pid = $delete_vars["pid"];
                $pokemon->delete($pid);
                http_response_code(200);
                echo json_encode(array("message" => "Pokemon was deleted."));
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "ID is required."));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed."));
            break;
    }

} catch (Exception $e) {
    // JWT token is invalid or expired
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized"));
    exit();
}

// Function to validate JWT token
function validateToken() {
    global $secret_key;
    $token = null;
    //print_r($_SERVER);
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
            $decoded = JWT::decode($token, $secret_key, array('HS256'));
            return true;
        } catch (Exception $e) {
            return false;
        }
    } else {
        return false;
    }
}

// Function to check if a token is blacklisted
function isTokenBlacklisted($token) {
    // Implement your logic to check if the token is blacklisted (e.g., check database)
    // Here, we'll just check if it exists in a session variable for demonstration purposes
    session_start();
    return isset($_SESSION['blacklist'][$token]);
}

?>
