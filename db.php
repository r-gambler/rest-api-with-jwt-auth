<?php
require __DIR__ . '/vendor/autoload.php'; // Include the Composer autoloader

use Firebase\JWT\JWT;

class Database {
    // Database connection variables
    private $hostname;
    private $dbname;
    private $username;
    private $password;
    private $conn;

    // Constructor
    public function __construct() {
        // Initialize connection variables
        $this->hostname = "localhost";
        $this->dbname = "pokemondb";
        $this->username = "root";
        $this->password = "";
    }

    // Method to establish database connection
    public function connect() {
        // Attempt to establish connection
        $this->conn = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);

        // Check for connection errors
        if ($this->conn->connect_errno) {
            // Print error message and exit if connection fails
            print_r($this->conn->connect_error);
            exit;
        } else {
            // Return connection object if connection successful
            return $this->conn;
        }
    }
}

// Example usage:
//$db = new Database();
//$connection = $db->connect();

?>
