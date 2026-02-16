<?php 

namespace PDF4Metform\Inc;

defined('ABSPATH') || exit;


class Settings{

    use Singleton;

    private function __construct(){
        add_action( 'add_meta_boxes', [$this, 'meta_box']);
    }

    public function meta_box(){
        add_meta_box(
            'metform_entries__pdf_link',
            'PDF',
            [$this, 'show_pdf_link'],
            'metform-entry',
            'side',
        );
    }

    public function show_pdf_link(){
        $options = get_option('pdf4metform', []);
        if( empty($options['installed']) ){
            echo sprintf(
                '<a href="admin.php?page=pdf-for-metform-page">%s</a>', 
                'Install PDF Fonts'
            );
            return;
        }
        $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        $nonce   = wp_create_nonce('pdf-for-metform-download');
        printf(
            '<a href="post.php?post=%s&action=edit&metform-pdf=true&nonce=%s">Download PDF</a>', 
            esc_attr( $post_id ), 
            esc_attr( $nonce )
        );
    }

}