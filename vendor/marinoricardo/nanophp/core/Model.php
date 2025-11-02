<?php
namespace Core;

abstract class Model
{
    protected static string $table;

    protected array $wheres = [];
    protected array $orWheres = [];
    protected ?string $orderBy = null;
    protected ?int $limit = null;
    protected ?int $offset = null;

    // Inicia o query builder
    public static function query(): static
    {
        return new static();
    }

    public function where(string $column, string|int $value): static
    {
        $this->wheres[] = [$column, '=', $value];
        return $this;
    }

    public function orWhere(string $column, string|int $value): static
    {
        $this->orWheres[] = [$column, '=', $value];
        return $this;
    }

    public function like(string $column, string $value): static
    {
        $this->wheres[] = [$column, 'LIKE', "%$value%"];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy = "$column $direction";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    // Retorna todos os registros do query builder
    public function get(): array
    {
        [$sql, $params] = $this->buildSelect();
        return Database::query($sql, $params);
    }

    // Retorna apenas o primeiro registro
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    // Paginação
    public function paginate(int $perPage = 10, int $page = 1): array
    {
        $this->limit($perPage)->offset(($page - 1) * $perPage);
        $items = $this->get();

        // Total de registros
        [$countSql, $params] = $this->buildCount();
        $total = Database::query($countSql, $params)[0]['total'] ?? 0;

        return [
            'items' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    public static function getAll(): array
    {
        $data = Database::query("SELECT * FROM `" . static::$table . "`");
        return ['items' => $data];
    }

    public static function find(int|string $id): ?array
    {
        return Database::find(static::$table, $id);
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function update(int|string $id, array $data): bool
    {
        return Database::update(static::$table, $id, $data);
    }

    public static function delete(int|string $id): bool
    {
        return Database::delete(static::$table, $id);
    }

    // --- Helpers internos ---
    protected function buildSelect(): array
    {
        $sql = "SELECT * FROM `" . static::$table . "`";
        $params = [];

        $clauses = [];
        foreach ($this->wheres as $i => [$col, $op, $val]) {
            $key = "w$i";
            $clauses[] = "$col $op :$key";
            $params[$key] = $val;
        }

        foreach ($this->orWheres as $i => [$col, $op, $val]) {
            $key = "ow$i";
            $clauses[] = "OR $col $op :$key";
            $params[$key] = $val;
        }

        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY " . $this->orderBy;
        }

        if ($this->limit) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset) {
            $sql .= " OFFSET " . $this->offset;
        }

        return [$sql, $params];
    }

    protected function buildCount(): array
    {
        $sql = "SELECT COUNT(*) as total FROM `" . static::$table . "`";
        $params = [];

        $clauses = [];
        foreach ($this->wheres as $i => [$col, $op, $val]) {
            $key = "w$i";
            $clauses[] = "$col $op :$key";
            $params[$key] = $val;
        }

        foreach ($this->orWheres as $i => [$col, $op, $val]) {
            $key = "ow$i";
            $clauses[] = "OR $col $op :$key";
            $params[$key] = $val;
        }

        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        return [$sql, $params];
    }
}
