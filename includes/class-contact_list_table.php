<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class NKLAB_WPCF7SN_Contact_List_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;

        parent::__construct( array(
            'singular' => 'post',
            'plural'   => 'posts',
            'ajax'     => false
        ) );
    }

    // カラムのデフォルト設定
    function column_default( $item, $column_name ) {
        return '';
    }

    // 列の識別子の設定
    function get_columns() {
        return $columns = array(
            "setting" => __( 'Setting', ContactForm7_Serial_Numbers::DOMAIN )
        );
    }

    function column_title( $item ) {
        return '<strong>' . esc_html( $item->post_title ) . '</strong>';
    }

    function column_setting( $item ) {

        $output = '';

        $item_id = intval( $item->ID );
        $html_encoded_id = esc_html( $item_id );

        $mail_tag = sprintf( '[cf7_serial_number_%1$d]', $item_id );

        $type   = ( get_option('nklab_wpcf7sn_type_'   . $item_id ) ) ? intval( get_option('nklab_wpcf7sn_type_'   . $item_id ) ) : 1;
        $count  = ( get_option('nklab_wpcf7sn_count_'  . $item_id ) ) ? intval( get_option('nklab_wpcf7sn_count_'  . $item_id ) ) : 0;
        $ditits = ( get_option('nklab_wpcf7sn_digits_' . $item_id ) ) ? intval( get_option('nklab_wpcf7sn_digits_' . $item_id ) ) : 0;
        $prefix = ( get_option('nklab_wpcf7sn_prefix_' . $item_id ) ) ?         get_option('nklab_wpcf7sn_prefix_' . $item_id )   : '';

        $output .= "\n" . '<div id="wpcf7sn_setting_' . $item_id . '" class="clearfix">'
            . '<form method="post" action="options.php">'
            . wp_nonce_field( 'update-options' )
            . '  <input type="hidden" name="action" value="update" />'
            . '  <input type="hidden" name="page_options" value="nklab_wpcf7sn_type_' . $html_encoded_id . ',nklab_wpcf7sn_digits_' . $html_encoded_id . ',nklab_wpcf7sn_prefix_' . $html_encoded_id . '" />'
            . '  <div class="wpcf7sn_setting_title">'
            . '    <strong>' . esc_html( $item->post_title ) . '</strong>'
            . '  </div>'
            . '  <div class="wpcf7sn_setting_col_left">'
            . '    <dl>'
            . '      <dt><span class="title">' . __( 'Display type', ContactForm7_Serial_Numbers::DOMAIN ) . '</span></dt>'
            . '      <dd>'
            . '        <p class="setting_body">'
            . '          <input type="radio" name="nklab_wpcf7sn_type_' . $html_encoded_id . '" value="1" ' . ( $type == 1  ? 'checked' : '' ) . ' /><label>' . __( 'Serial Number', ContactForm7_Serial_Numbers::DOMAIN ) . '</label> <span style="padding-left: 10px;"> ' . sprintf( __( '( Now Count : %1$d )', ContactForm7_Serial_Numbers::DOMAIN ), $count ) . '</span><br />'
            . '          <span class="indent"><label>' . __( 'Number of digits', ContactForm7_Serial_Numbers::DOMAIN ) . '</label> : <input type="text" name="nklab_wpcf7sn_digits_' . $html_encoded_id . '" value="' . $ditits . '" size="2" maxlength="2" /></span>'
            . '        </p>'
            . '        <p class="setting_body">'
            . '          <input type="radio" name="nklab_wpcf7sn_type_' . $html_encoded_id . '" value="2" ' . ( $type == 2 ? 'checked' : '' ) . ' /><label>' . __( 'Timestamp (ms)', ContactForm7_Serial_Numbers::DOMAIN ) . '</label>'
            . '        </p>'
            . '      </dd>'
            . '    </dl>'
            . '  </div>'
            . '  <div class="wpcf7sn_setting_col_center">'
            . '    <dl>'
            . '      <dt><span class="title">' . __( 'Prefix', ContactForm7_Serial_Numbers::DOMAIN ) . '</span></dt>'
            . '      <dd>'
            . '        <p class="setting_body">'
            . '          <input type="text" name="nklab_wpcf7sn_prefix_' . $html_encoded_id . '" value="' . $prefix . '" size="15" maxlength="10" />'
            . '        </p>'
            . '      </dd>'
            . '    </dl>'
            . '  </div>'
            . '  <div class="wpcf7sn_setting_col_right">'
            . '    <dl>'
            . '      <dt><span class="title">' . __( 'Mail Tags', ContactForm7_Serial_Numbers::DOMAIN ) . '</span></dt>'
            . '      <dd>'
            . '        <p class="setting_body">'
            . '          <input type="text" readonly="readonly" value="' . esc_attr( $mail_tag ) . '" size="35" />'
            . '        </p>'
            . '      </dd>'
            . '    </dl>'
            . '    <div class="wpcf7sn_setting_submit">'
            . '      <p class="wpcf7sn_submit"><input type="submit" class="button-primary" value="' . __( 'Update', ContactForm7_Serial_Numbers::DOMAIN ) . '" /></p>'
            . '    </div>'
            . '  </div>'
            . '</form>'
            . '</div>';

        return trim( $output );
    }

    // ソート列の設定
    function get_sortable_columns() {
        $sortable_columns = array();

        return $sortable_columns;
    }

    // 一括操作の設定
    function get_bulk_actions() {
        $actions = array();

        return $actions;
    }

    // 一括操作の適用ボタン押下時の処理
    function process_bulk_action() {
        // none
    }

    // テーブル情報の設定
    function prepare_items() {
        // 1ページに表示する件数
        $per_page = 10;

        // 列の設定
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // 列ヘッダーの設定
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // 一括操作の設定
        $this->process_bulk_action();

        // データの設定
        $args = array(
            'post_type' => 'wpcf7_contact_form',
            'post_status' => 'any',
            'posts_per_page' => $per_page,
            'orderby' => 'title',
            'order' => 'ASC',
            'offset' => ( $this->get_pagenum() - 1 ) * $per_page
        );

        $data = query_posts( $args );

        $current_page = $this->get_pagenum();

        $total_items = count($data);
        $total_pages = ceil( $total_items / $per_page );

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ) );
    }
}

?>
