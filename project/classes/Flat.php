<?php
require_once __DIR__ . '/../dbconfig.inc.php';

class Flat
{
    private PDO $pdo;
    private array $data = [];

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? getDatabaseConnection();
    }

    public function loadById(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT * FROM flats WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return false;
        $this->data = $row;
        return true;
    }

    public function __get(string $key) {
        return $this->data[$key] ?? null;
    }

    public function getImages(): array {
        if (empty($this->data['id'])) return [];
        $stmt = $this->pdo->prepare("SELECT image_path FROM images WHERE flat_id = :id ORDER BY id ASC");
        $stmt->execute([':id' => $this->data['id']]);
        return $stmt->fetchAll();
    }

    public function getViewingTimes(): array {
        if (empty($this->data['id'])) return [];
        $stmt = $this->pdo->prepare("
            SELECT day_of_week, time_from, time_to, contact_phone, is_booked
            FROM viewing_times
            WHERE flat_id = :id
            ORDER BY day_of_week, time_from
        ");
        $stmt->execute([':id' => $this->data['id']]);
        return $stmt->fetchAll();
    }
}
