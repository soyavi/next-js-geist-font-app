<?php
require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $conn;
    protected $table;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll($conditions = [], $limit = null, $offset = null) {
        $query = "SELECT * FROM " . $this->table;
        
        if (!empty($conditions)) {
            $query .= " WHERE ";
            $whereClauses = [];
            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = :$key";
            }
            $query .= implode(" AND ", $whereClauses);
        }

        if ($limit !== null) {
            $query .= " LIMIT :limit";
            if ($offset !== null) {
                $query .= " OFFSET :offset";
            }
        }

        $stmt = $this->conn->prepare($query);

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($limit !== null) {
            $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $fields = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        
        $query = "INSERT INTO " . $this->table . " ($fields) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $setClauses = [];
        foreach (array_keys($data) as $key) {
            $setClauses[] = "$key = :$key";
        }
        $setClause = implode(", ", $setClauses);

        $query = "UPDATE " . $this->table . " SET $setClause WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(":id", $id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    protected function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    protected function commit() {
        return $this->conn->commit();
    }

    protected function rollback() {
        return $this->conn->rollBack();
    }
}
