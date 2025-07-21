<?php

namespace PdSeoOptimizer;

if (!class_exists('\WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SeoLogsTable extends \WP_List_Table {
    private array $logs;

    public function __construct(array $logs) {
        parent::__construct([
            'singular' => 'log',
            'plural'   => 'logs',
            'ajax'     => false,
        ]);

        $this->logs = $logs;
    }

    public function get_columns() {
        return [
            'post'      => 'Post',
            'details'   => 'Details',
            'status'    => 'Status',
            'log_time'  => 'Time',
        ];
    }

    protected function column_default($item, $column_name) {
        if ($column_name === 'post') {
            $postId = (int) $item->post_id;
            $post = get_post($postId);
            if ($post) {
                $title = esc_html(get_the_title($post));
                $editLink = esc_url(get_edit_post_link($postId));
                return sprintf('<a href="%s" target="_blank">%s</a>', $editLink, $title);
            } else {
                return '<em>Post not found</em>';
            }
        }

        if ($column_name === 'details') {
            $decoded = json_decode($item->$column_name, true);
            if (!is_array($decoded)) {
                return esc_html($item->$column_name);
            }
        
            $output = '<ul style="margin: 0; padding-left: 1em;">';
            foreach ($decoded as $key => $value) {
                $output .= '<li><strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong> ' . esc_html($value) . '</li>';
            }
            $output .= '</ul>';
        
            return $output;
        }

        return esc_html($item->$column_name ?? '');
    }

    public function prepare_items() {
        $columns  = $this->get_columns();
        $hidden   = [];
        $sortable = [];

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items = $this->logs;
    }
}
