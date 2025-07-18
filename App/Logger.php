<?php

namespace PdSeoOptimizer;

use wpdb;

class Logger {
    private $wpdb;
    private $tableName;
    private $id = 'id';
    private $postId = 'post_id';
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
            {$this->postId} BIGINT(20) UNSIGNED NULL,
            {$this->details} TEXT NULL,
            {$this->status} VARCHAR(20) NOT NULL DEFAULT 'update',
            {$this->logTime} DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY {$this->id} ({$this->id})
        ) $charsetCollate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function addLog(?int $postId = null, string $status = "update", array $details  = []) {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO {$this->tableName} ({$this->postId}, {$this->details}, {$this->status}, {$this->logTime}) 
                VALUES (%d, %s, %s, %s)",
                $postId,
                wp_json_encode($details, JSON_UNESCAPED_UNICODE),
                $status,
                current_time('mysql')
            )
        );
    }

    public function getLogs(int $limit = 50) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->tableName} ORDER BY {$this->logTime} DESC LIMIT %d",
                $limit
            )
        );
    }
}