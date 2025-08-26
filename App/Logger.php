<?php

namespace PdSeoOptimizer;

use wpdb;

class Logger {
    private $wpdb;
    private $tableName;
    private $id = 'id';
    private $objectId = 'object_id';
    private $objectType = 'object_type';
    private $details = 'details';
    private $status = 'status';
    private $logTime = 'log_time';

    private static $instance = null;

    private function __construct(
        wpdb $wpdb,
    ) {
        $this->wpdb = $wpdb;
        $this->tableName = esc_sql($this->wpdb->prefix . 'pd_seo_optimizer_logs');
        $this->createTable();
    }

    public static function getInstance() {
        global $wpdb;
        if (self::$instance === null) {
            self::$instance = new Logger($wpdb);
        }
        return self::$instance;
    }

    private function createTable() {
        $table_exists = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $this->tableName
            )
        );

        if ($table_exists) {
            return;
        }

        $charsetCollate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->tableName} (
            {$this->id} BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            {$this->objectId} BIGINT(20) UNSIGNED NOT NULL,
            {$this->objectType} VARCHAR(20) NOT NULL,
            {$this->details} TEXT NULL,
            {$this->status} VARCHAR(20) NOT NULL DEFAULT 'update',
            {$this->logTime} DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY {$this->id} ({$this->id})
        ) $charsetCollate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function addLog(int $objectId, string $objectType, string $status = "update", array $details = []) {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO {$this->tableName} ({$this->objectId}, {$this->objectType}, {$this->details}, {$this->status}, {$this->logTime}) 
                VALUES (%d, %s, %s, %s, %s)",
                $objectId,
                $objectType,
                wp_json_encode($details, JSON_UNESCAPED_UNICODE),
                $status,
                current_time('mysql')
            )
        );
    }

    public function getLogs(int $limit, int $offset, string $search = '', ?string $objectType = null) {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "{$this->details} LIKE %s";
            $params[] = "%{$search}%";
        }

        if ($objectType !== null) {
            $where[] = "{$this->objectType} = %s";
            $params[] = $objectType;
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT * FROM {$this->tableName} {$whereSql} ORDER BY {$this->logTime} DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, ...$params)
        );
    }

    public function countLogs(string $search = '', ?string $objectType = null) {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "{$this->details} LIKE %s";
            $params[] = "%{$search}%";
        }

        if ($objectType !== null) {
            $where[] = "{$this->objectType} = %s";
            $params[] = $objectType;
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT COUNT(*) FROM {$this->tableName} {$whereSql}";
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare($sql, ...$params)
        );
    }

    public function deleteLogs(array $ids) {
        if (empty($ids)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->tableName} WHERE {$this->id} IN ($placeholders)",
                ...$ids
            )
        );
    }
}
