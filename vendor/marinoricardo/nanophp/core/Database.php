<?php
namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $conn = null;

    public static function connect(string $envPath = null): PDO {
        if (self::$conn === null) {
            // Carrega o .env do projeto apenas se não estiver carregado
            if (!isset($_ENV['DB_HOST'])) {
                Env::load($envPath ?? __DIR__ . '/../.env');
            }

            $host = Env::get('DB_HOST', 'localhost');
            $db   = Env::get('DB_NAME', '');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASS', '');
            $charset = Env::get('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

            try {
                self::$conn = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                Response::error('Falha na conexão: ' . $e->getMessage(), 500);
            }
        }

        return self::$conn;
    }


    // Executa query genérica
    public static function query(string $sql, array $params = []): array {
        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Response::error('Erro no banco de dados: ' . $e->getMessage(), 500);
        }

        return [];
    }

    // Métodos para abstração de CRUD
    public static function find(string $table, int|string $id): ?array {
        $stmt = self::connect()->prepare("SELECT * FROM `$table` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function insert(string $table, array $data): int {
        $cols = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = self::connect()->prepare("INSERT INTO `$table` ($cols) VALUES ($placeholders)");
        $stmt->execute($data);
        return (int) self::connect()->lastInsertId();
    }

    public static function update(string $table, int|string $id, array $data): bool {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;
        $stmt = self::connect()->prepare("UPDATE `$table` SET $set WHERE id = :id");
        return $stmt->execute($data);
    }

    public static function delete(string $table, int|string $id): bool {
        $stmt = self::connect()->prepare("DELETE FROM `$table` WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
