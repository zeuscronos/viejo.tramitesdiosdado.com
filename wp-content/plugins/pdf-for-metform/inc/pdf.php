<?php
namespace PDF4Metform\Inc;

defined('ABSPATH') || exit;

use Mpdf\Mpdf;

class PDF{

   use Singleton;

    private function __construct() {
		add_action( 'admin_init', array(&$this, 'output'));
	}

    public function output( $entry_id = '', $pdf_filepath  = '' ){ 

        if( empty( $_GET['metform-pdf'] ) && empty( $pdf_filepath ) ) 
            return;

        if( empty( $_GET['post'] ) && empty( $pdf_filepath ) ) 
            return;

        $nonce = sanitize_text_field( wp_unslash( $_GET['nonce'] ) );

        if( ! wp_verify_nonce( $nonce , 'pdf-for-metform-download') )
            return;

        $upload_dir    = wp_upload_dir();
        $fontDir       = $upload_dir['basedir'].'/pdf4metform/ttfonts';
        $options       = get_option('pdf4metform', []);
        $font          = isset( $options['font'] ) ? $options['font'] : '';
        $font          = str_replace('.ttf', '', $font); 
        $margin_left   = isset( $options['margin_left'] ) ? $options['margin_left'] : '';
        $margin_right  = isset( $options['margin_right'] ) ? $options['margin_right'] : '';
        $margin_top    = isset( $options['margin_top'] ) ? $options['margin_top'] : '';
        $margin_bottom = isset( $options['margin_bottom'] ) ? $options['margin_bottom'] : '';
 

        $entry_id    = isset($_GET['post']) ? (int) $_GET['post'] : $entry_id;
        $form_id     = get_post_meta($entry_id, 'metform_entries__form_id', true);

        $entry_title = get_the_title( $entry_id );
        $form_title  = get_the_title( $form_id );
        $form_data   = get_post_meta( $entry_id, 'metform_entries__form_data', true );
        // $map_data = \MetForm\Core\Entries\Action::instance()->get_fields($form_id);
    
        // $map_data = json_decode(json_encode($map_data), true);
        $form_html = \MetForm\Core\Entries\Form_Data::format_form_data($form_id, $form_data);

        $mpdf = new Mpdf( array( 
			'tempDir' => $upload_dir['basedir'].'/pdf4metform/',
            'fontDir' => $fontDir,
			'default_font' => $font,
			'format' => 'A4',
			'margin_left' => $margin_left,
            'margin_right' => $margin_right,
            'margin_top' => $margin_top,
            'margin_bottom' => $margin_bottom,
			'autoLangToFont' => true
		) );

		$mpdf->autoScriptToLang = true;
		$mpdf->baseScript = 1;
		$mpdf->autoVietnamese = true;
		$mpdf->autoArabic = true;

		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
        $shortcodes = array_map(function( $key){
                        return "[$key]";
                    }, array_keys( $form_data ));
        $html = file_get_contents( __DIR__ . '/default-template.php' );  
        $html = str_replace( [
                    '[all_data]', '[entry_title]', '[form_title]'], 
                    [$form_html, $entry_title, $form_title],
                    $html
                );

        $html  = str_replace($shortcodes, array_values($form_data), $html);
        $mpdf->WriteHTML( $html );
        if( empty( $pdf_filepath ) ){
		    $mpdf->Output('entry-id-'.$entry_id.'.pdf', 'I');
            die;
        }
        if( !empty( $pdf_filepath ) ){
		    $mpdf->Output( $pdf_filepath . '/entry-id-'.$entry_id.'.pdf', 'F');
        }
    }
}