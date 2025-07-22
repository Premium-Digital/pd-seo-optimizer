<?php

namespace PdSeoOptimizer;

if (!class_exists('\WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use PdSeoOptimizer\Logger;

class SeoLogsTable extends \WP_List_Table {
    private $logger;

    public function __construct() {
        global $wpdb;
        $this->logger = Logger::getInstance($wpdb);
        parent::__construct([
            'singular' => 'log',
            'plural'   => 'logs',
            'ajax'     => false,
        ]);
    }

    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'post'      => 'Post',
            'details'   => 'Details',
            'status'    => 'Status',
            'log_time'  => 'Time',
        ];
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item->id
        );
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
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
        $this->process_bulk_action();
        $this->_column_headers = [$columns, $hidden, $sortable];
        $per_page = 25;
        $paged = isset($_REQUEST['paged']) && is_numeric($_REQUEST['paged']) ? max(1, intval($_REQUEST['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $this->items = $this->logger->getLogs($per_page, $offset, $search);
        $total_items = $this->logger->countLogs($search);

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();

            if (!empty($ids)) {
                $this->logger->deleteLogs($ids);
            }
        }
    }

    public function search_box($text, $input_id) {
        $input_value = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        printf(
            '<p class="search-box">
                <label class="screen-reader-text" for="%1$s">%2$s:</label>
                <input type="search" id="%1$s" name="s" value="%3$s" />
                <input type="submit" id="%1$s-submit" class="button" value="%2$s" />
            </p>',
            esc_attr($input_id),
            esc_html($text),
            esc_attr($input_value)
        );
    }

}
