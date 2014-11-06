<?php
/*
Plugin Name: Contact Form 7 Serial Numbers
Version: 0.5
Description: お問い合わせフォームプラグイン Contact Form 7 にて問い合わせ番号をふる
Author: Kiminori KATO
Author URI: http://www.29lab.jp/
Text Domain: contact-form-7-serial-numbers
Domain Path: /languages
*/

define( 'NKLAB_WPCF7SN_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
require_once NKLAB_WPCF7SN_PLUGIN_DIR . '/includes/class-contact_list_table.php';


class ContactForm7_Serial_Numbers {

    private $options;
    const OPTION_SAVE_FILE = 'wpcf7sn_options.txt';
    const DOMAIN = 'contact-form-7-serial-numbers';

    function __construct() {
        $this->options = $this->get_plugin_options();

        // プラグインが有効化された時に実行されるメソッドを登録
        if ( function_exists( 'register_activation_hook' ) )
            register_activation_hook( __FILE__, array( &$this, 'activation' ) );
        // プラグインが停止されたときに実行されるメソッドを登録
        if ( function_exists( 'register_deactivation_hook' ) )
            register_deactivation_hook( __FILE__, array( &$this, 'deactivation' ) );

        // アクションフックの設定
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
        add_action( 'wpcf7_before_send_mail', array( &$this, 'increment_count' ) );

        // フィルターフックの設定
        add_filter( 'wpcf7_special_mail_tags', array( &$this, 'special_mail_tags' ), 10, 2 );

        // 言語ファイルの読み込み
        load_plugin_textdomain( self::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    // plugin activation
    function activation() {
        $option_file = dirname( __FILE__ ) . '/' . self::OPTION_SAVE_FILE;
        if ( file_exists( $option_file ) ) {
            $wk_options = unserialize( file_get_contents( $option_file ) );
            if ( $wk_options != $this->options ) {
                $this->options = $wk_options;
                foreach ( $this->options as $key=>$value ) {
                    update_option( $key, $value );
                }
                unlink( $option_file );
            }
        }
    }

    // plugin deactivation
    function deactivation() {
        $option_file = dirname( __FILE__ ) . '/' . self::OPTION_SAVE_FILE;
        $wk_options = serialize( $this->options );
        if ( file_put_contents( $option_file, $wk_options ) && file_exists( $option_file ) ) {
            foreach( $this->options as $key=>$value ) {
                delete_option( $key );
            }
        }
    }

    // get plugin options
    function get_plugin_options() {
        global $wpdb;
        $values = array();
        $results = $wpdb->get_results( "
            SELECT *
              FROM $wpdb->options
             WHERE 1 = 1
               AND option_name like 'nklab_wpcf7sn_%'
             ORDER BY option_name
        " );

        foreach ( $results as $result ) {
            $values[ $result->option_name ] = $result->option_value;
        }

        return $values;
    }

    // admin init
    function admin_init() {
        wp_enqueue_style( 'contact-form-7-serial-numbers', plugin_dir_url( __FILE__ ) . 'css/style.css' );
    }

    // admin menu
    function admin_menu() {
        add_options_page(
            __( 'Contact Form 7 Serial Numbers', self::DOMAIN ),
            __( 'Contact Form 7 Serial Numbers', self::DOMAIN ),
            'level_8',
            __FILE__,
            array( &$this, 'wpcf7sn_admin_opt_page' )
        );
    }

    // option page
    function wpcf7sn_admin_opt_page() {
        $list_table = new NKLAB_WPCF7SN_Contact_List_Table();
        $list_table->prepare_items();
?>
<div class="wrap">
    <h2><?php _e( 'Contact Form 7 Serial Numbers', self::DOMAIN ); ?></h2>
    <p></p>
    <p><?php _e( 'Copy the code of mail tags and paste it into any location ( ex. message body or subject etc.) of mail templates of Contact Form 7.', self::DOMAIN ); ?></p>

    <?php $list_table->display(); ?>
</div>
<?php
    }

    // increment count
    function increment_count( $contactform ) {
        // get form id
        $id = intval( $contactform->id() );

        // get count
        $count = ( get_option( 'nklab_wpcf7sn_count_' . $id ) ) ? intval( get_option( 'nklab_wpcf7sn_count_' . $id ) ) : 0;

        update_option( 'nklab_wpcf7sn_count_' . $id, intval( $count + 1 ) );
    }

    // special mail tags
    function special_mail_tags( $output, $name ) {
        if ( ! isset( $_POST['_wpcf7_unit_tag'] ) || empty( $_POST['_wpcf7_unit_tag'] ) ) return $output;
        $name = preg_replace( '/^wpcf7\./', '_', $name );

        if ( 'cf7_serial_number_' == substr( $name, 0, 18 ) ) {
            // form id の取得
            $id = intval( substr( $name, 18 ) );

            // 通し番号設定の取得
            $digits = ( get_option( 'nklab_wpcf7sn_digits_' . $id ) ) ? intval( get_option( 'nklab_wpcf7sn_digits_' . $id ) ) : 0;
            $type   = ( get_option( 'nklab_wpcf7sn_type_' . $id ) ) ? intval( get_option( 'nklab_wpcf7sn_type_' . $id ) ) : 1;
            $prefix = ( get_option( 'nklab_wpcf7sn_prefix_' . $id ) ) ? get_option( 'nklab_wpcf7sn_prefix_' . $id ) : '';
            $count  = ( get_option( 'nklab_wpcf7sn_count_' . $id ) ) ? intval( get_option( 'nklab_wpcf7sn_count_' . $id ) ) : 0;

            switch( $type ) {
                case 1:
                    // 番号
                    $output = $count;
                    if ( $digits ) {
                        $output = sprintf( "%0" . $digits . "d", $output );
                    }
                    break;
                case 2:
                    // タイムスタンプ
                    $output = microtime( true ) * 10000;
                    break;
                default:
                    $output = '';
            }
            $output = $prefix . $output;
        }
        return $output;
    }
}

$NKLAB_WPCF7_SerialNumbers = new ContactForm7_Serial_Numbers();

?>
