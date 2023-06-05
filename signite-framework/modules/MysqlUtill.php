<?php

// this file helps developer to create and manage database and tables structure
// without having to write SQL queries

namespace Signite\Modules;

use mysqli;

class MYSQL_Util {
    public function __construct($host, $username, $password, $database, $tables) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->tables = $tables;
    }

    public function connect() : bool|mysqli {
        return ($this->createDatabase() 
        && $this->createTables($this->tables)) ? $this->generateConnectionSocket() : false;
    }

    private function generateConnectionSocket() {
        $connection = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }
        return $connection;
    }

    private function createDatabase(): bool {
        $conn = new mysqli($this->host, $this->username, $this->password);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->database;
        if ($conn->query($sql) === TRUE) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false;
        }
    }

    private function createTables(Array $tables): bool {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        foreach ($tables as $table) {
            $conn->query($table->getAllSqlCode());
        }
        $conn->close();
        return true;
    }
}

class Table {
    public function __construct($name, $columns) {
        $this->name = $name;
        $this->columns = $columns;
    }

    public function getTableName(): String {
        return $this->name;
    }

    public function getAllSqlCode(): string {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->name . " (";
        foreach ($this->columns as $column) {
            $sql .= $column->getSqlCode() . ", ";
        }
        $sql = substr($sql, 0, strlen($sql) - 2);
        $sql .= ")";
        return $sql;
    }
}

class Column {

    private $_nonLengthTypes = ["datetime", "text", "json"];

    public function __construct($name, $type, $length, $isNullable, $isPrimaryKey, $isAutoIncrement, $defaultValue = null) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->isNullable = $isNullable;
        $this->isPrimaryKey = $isPrimaryKey;
        $this->isAutoIncrement = $isAutoIncrement;
        $this->defaultValue = $defaultValue;
    }

    public function getSqlCode(): string {
        $sql = $this->name . " " . $this->type;
        if ($this->length !== null && !in_array($this->type, $this->_nonLengthTypes)) {
            $sql .= "(" . $this->length . ")";
        }
        if ($this->isNullable) {
            $sql .= " NULL";
        } else {
            $sql .= " NOT NULL";
        }
        if ($this->isPrimaryKey) {
            $sql .= " PRIMARY KEY";
        }
        if ($this->isAutoIncrement) {
            $sql .= " AUTO_INCREMENT";
        }
        if ($this->defaultValue !== null) {
            $sql .= " DEFAULT " . (is_string($this->defaultValue) ? "'" . $this->defaultValue . "'" : $this->defaultValue);
        }
        return $sql;
    }
}