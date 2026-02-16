<?php 
/** 
 * Plugin Name: PDF For Metform
 * Plugin URI: https://wpdebuglog.com/
 * Description: PDF Plugin for metform 
 * Text Domain: pdf-for-metform
 * Version: 1.0.0
 * Author: wpdebuglog
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require plugin_dir_path(__FILE__).'/vendor/autoload.php';
require plugin_dir_path(__FILE__).'/inc/singleton.php';
require plugin_dir_path(__FILE__).'/inc/install.php';
require plugin_dir_path(__FILE__).'/inc/settings.php';
require plugin_dir_path(__FILE__).'/inc/pdf.php';

PDF4Metform\Inc\Settings::instance();
PDF4Metform\Inc\Pdf::instance();
PDF4Metform\Inc\Sub_menu::set_path( plugin_dir_url( __FILE__), plugin_dir_path( __FILE__) );
PDF4Metform\Inc\Sub_menu::instance();

function pdf4metform_on_activate( $network_wide ){

    update_option('pdf4metform', [
        'font' => 'DejaVuSansCondensed.ttf',
        'margin_left' => 15, 
        'margin_right' => 15,
        'margin_bottom' => 15,
        'margin_top' => 15
    ]);

    $upload_dir  = wp_upload_dir();
    $dirname     = $upload_dir['basedir'].'/pdf4metform';

    if ( ! file_exists( $dirname ) ) {
        wp_mkdir_p( $dirname );
        $fp = fopen( $dirname.'/index.php', 'w');
        fwrite($fp, "<?php \n\t // Silence is golden.");
        fclose( $fp );
    }
    $template_dir = $dirname . '/template';
    if ( ! file_exists( $template_dir ) ) {
        wp_mkdir_p( $template_dir );
        $fp = fopen( $template_dir.'/index.php', 'w');
        fwrite($fp, "<?php \n\t // Silence is golden.");
        fclose( $fp );
    }
}

register_activation_hook( __FILE__, 'pdf4metform_on_activate' );

 