<?php

namespace PdSeoOptimizer;

use wpdb;

class Logger {
    private $wpdb;
    private $tableName;
    private $id = 'id';
    private $logTime = 'log_time';
    private $message = 'message';
    private $postId = 'post_id';
    private $status = 'status';

    private static $instance = null;

    private function __construct(
        wpdb $wpdb,
    ) {
        $this->wpdb = $wpdb;
        $this->tableName = esc_sql($this->wpdb->prefix . 'pd_seo_optimizer_logs');
        $this->createTable();
    }

    public static function getInstance(wpdb $wpdb) {
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
            {$this->message} TEXT NOT NULL,
            {$this->postId} BIGINT(20) UNSIGNED NULL,
            {$this->status} VARCHAR(50) NOT NULL,
            {$this->logTime} DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY {$this->id} ({$this->id})
        ) $charsetCollate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function addLog(string $message, ?int $postId = null, string $status = 'info') {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT INTO {$this->tableName} ({$this->message}, {$this->postId}, {$this->status}, {$this->logTime}) 
                VALUES (%s, %d, %s, %s)",
                sanitize_text_field($message),
                $postId,
                sanitize_text_field($status),
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