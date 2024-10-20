<?php

class Pokemon {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function read() {
        $sql = "SELECT * FROM pokemontable";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function readOne($pid) {
        $sql = "SELECT * FROM pokemontable WHERE pid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    public function create($pokemonname, $pokemontype, $pokemonlocation) {
        $sql = "INSERT INTO pokemontable (pokemonname, pokemontype, pokemonlocation) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $pokemonname, $pokemontype, $pokemonlocation);
        $stmt->execute();
        return $stmt;
    }

    public function update($pid, $pokemonname, $pokemontype, $pokemonlocation) {
        $sql = "UPDATE pokemontable SET pokemonname = ?, pokemontype = ?, pokemonlocation = ? WHERE pid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $pokemonname, $pokemontype, $pokemonlocation, $pid);
        $stmt->execute();
        return $stmt;
    }

    public function delete($pid) {
        $sql = "DELETE FROM pokemontable WHERE pid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        return $stmt;
    }
}

?>
