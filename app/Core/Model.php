<?php

namespace App\Core;

/**
 * Model co so - moi Model cu the (Car, Brand, User...) chi can khai bao
 * $table va $primaryKey, ke thua san cac thao tac CRUD dung prepared
 * statement (giu dung nguyen tac chong SQL injection da co trong toan bo
 * du an - khong bao gio noi chuoi truc tiep vao SQL).
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected \mysqli $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Tao ban ghi moi. $data la mang lien ket ['CotA' => giaTri, ...].
     * Tra ve ID vua tao.
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList = implode(', ', array_map(fn($c) => "`$c`", $columns));

        $sql = "INSERT INTO `{$this->table}` ($columnList) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        [$types, $values] = $this->bindTypesAndValues($data);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return (int) $stmt->insert_id;
    }

    /**
     * Cap nhat ban ghi theo khoa chinh. $data la mang lien ket cot->gia tri.
     */
    public function update(int $id, array $data): bool
    {
        $columns = array_keys($data);
        $setClause = implode(', ', array_map(fn($c) => "`$c` = ?", $columns));

        $sql = "UPDATE `{$this->table}` SET $setClause WHERE `{$this->primaryKey}` = ?";
        $stmt = $this->db->prepare($sql);

        [$types, $values] = $this->bindTypesAndValues($data);
        $types .= 'i';
        $values[] = $id;
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Doan chuoi type ky tu cho bind_param dua tren kieu du lieu PHP thuc
     * te cua tung gia tri (int->i, float->d, con lai->s).
     *
     * @return array{0: string, 1: array}
     */
    private function bindTypesAndValues(array $data): array
    {
        $types = '';
        $values = [];
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        return [$types, $values];
    }
}
