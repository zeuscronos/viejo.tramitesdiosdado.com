<?php 

namespace PDF4Metform\Inc;

defined('ABSPATH') || exit;

/**
 * Sub menu class
 *
 */
class Sub_menu {

    use Singleton;

    private static $plugin_dir_url;
    private static $plugin_dir_path;

    private $font_url = 'https://raw.githubusercontent.com/arshidkv12/ttfonts/main/';


    public static function set_path( $plugin_dir_url, $plugin_dir_path){
        static::$plugin_dir_url  = $plugin_dir_url;
        static::$plugin_dir_path = $plugin_dir_path;
    }

	private function __construct() {
		add_action( 'admin_menu', array(&$this, 'register_sub_menu'), 99 );
        add_action('admin_enqueue_scripts', array(&$this, 'admin_page_css'));
        add_action('wp_ajax_pdf4metform_download_action', array($this, 'download'));
        add_action('wp_ajax_pdf4metform_download_font', array($this, 'download_font'));
	}

	public function register_sub_menu() {
		add_submenu_page( 
			'metform-menu', 'PDF', 'PDF', 'manage_options', 'pdf-for-metform-page', array($this, 'submenu_page_callback')
		);
	}

    public function admin_page_css(){
        $screen = get_current_screen();
        if ($screen->id === 'metform_page_pdf-for-metform-page') {
            wp_enqueue_style('pdf4-metform', static::$plugin_dir_url . '/admin.css', array(), '1.0.0');
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'pdf4-metform-js', static::$plugin_dir_url . '/admin.js', ['jquery'], '1.0.0', ['in_footer' => true] );
            wp_localize_script('pdf4-metform-js', 'pdf4metform', array(
                'ajax_url' => admin_url( 'admin-ajax.php' )
            ));
        }
    }

    public function submenu_page_callback(){
        $this->save_options();
        $options              = get_option('pdf4metform', []);
        $margin_left          = isset( $options['margin_left'] ) ? $options['margin_left'] : '';
        $margin_right         = isset( $options['margin_right'] ) ? $options['margin_right'] : '';
        $margin_top           = isset( $options['margin_top'] ) ? $options['margin_top'] : '';
        $margin_bottom        = isset( $options['margin_bottom'] ) ? $options['margin_bottom'] : '';
        ?>
		<div class="pdf4m wrap">
            <h2><?php esc_attr_e('PDF For Metforms', 'pdf-form-metform') ?></h2>
            <?php
            if( empty($options['installed'])):?>
                <h3><?php esc_attr_e('Install Fonts', 'pdf-form-metform') ?></h3>
                <button class="btn install" data-nonce="<?php echo wp_create_nonce('install-pdf-for-metform') ?>">
                    <?php esc_attr_e('Install Now', 'pdf-form-metform') ?>
                </button>
                <p><?php esc_attr_e( 'Please do not close this browser window', 'pdf-form-metform') ?> </p>
                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: 0"></div>
                </div>
                <textarea class="list-fonts"></textarea>
            <?php else: ?>
            <div class="form">
                <form action='' method="POST">
                    <?php wp_nonce_field('pdf-for-metform-options'); ?> 
                    <label for="font"><?php esc_attr_e('Font', 'pdf-form-metform') ?>
                        <div class="input-control">
                            <?php 
                                $json_path = static::$plugin_dir_path. '/inc/fonts.json';
                                $font_json = file_get_contents( $json_path );
                                $fonts     = json_decode( $font_json );
                            ?>
                            <select name="font" id="font" required>
                                <option value=""><?php esc_attr_e('Select Font', 'pdf-form-metform') ?></option>
                                <?php foreach($fonts as $font): ?>
                                    <option <?php echo isset($options['font']) && $font == $options['font'] ? 'selected' :'' ?> value="<?php echo esc_attr($font) ?>"><?php  echo esc_attr($font) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </label>
                    <label for="margin_left"><?php esc_attr_e('Margin Left (mm)', 'pdf-form-metform' ) ?>
                        <div class="input-control">
                            <input type="number" name="margin-left" value="<?php echo esc_attr( $margin_left ) ?>" id="margin-left" placeholder="<?php esc_attr_e("Margin Left", 'pdf-form-metform') ?>" required> 
                        </div>
                    </label>
                    <label for="margin_right"><?php esc_attr_e('Margin Right (mm)', 'pdf-form-metform' )?>
                        <div class="input-control">
                            <input type="number" name="margin-right" value="<?php echo esc_attr( $margin_right ) ?>" id="margin-right" placeholder="<?php esc_attr_e('Margin Right', 'pdf-form-metform') ?>" required> 
                        </div>
                    </label>
                    <label for="margin_top"><?php esc_attr_e( 'Margin Top (mm)', 'pdf-form-metform') ?>
                        <div class="input-control">
                            <input type="number" name="margin-top" value="<?php echo esc_attr( $margin_top ) ?>" id="margin-top" placeholder="<?php esc_attr_e('Margin Top', 'pdf-form-metform') ?>" required> 
                        </div>
                    </label>
                    <label for="margin_bottom"><?php esc_attr_e('Margin Bottom (mm)', 'pdf-form-metform') ?>
                        <div class="input-control">
                            <input type="number" name="margin-bottom" value="<?php echo esc_attr( $margin_bottom ) ?>" id="margin-bottom" placeholder="<?php esc_attr_e('Margin Bottom', 'pdf-form-metform') ?>" required> 
                        </div>
                    </label>
                    <label> 
                        <div class="input-control">
                            <input  class='button button-primary' type="submit" value="Submit"> 
                        </div>
                    </label>
                </form>
            </div>
            <?php endif ?>
		</div>
        <?php 
	}

    public function download(){

        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );

        if( ! wp_verify_nonce($nonce, 'install-pdf-for-metform') ){
            return;
        }

        $upload_dir  = wp_upload_dir();
        $destination = $upload_dir['basedir'].'/pdf4metform/ttfonts';

        if ( ! file_exists( $destination ) ) {
            wp_mkdir_p( $destination );
            $fp = fopen( $destination.'/index.php', 'w');
            fwrite($fp, "<?php \n\t // Silence is golden.");
            fclose( $fp );
        }

        $font_json = static::$plugin_dir_path. '/inc/fonts.json'; 
        echo file_get_contents( $font_json );
        die;
    }

    public function download_font(){

        if( empty( $_POST['font'] ) || empty($_POST['nonce']) ){
           return; 
        }

        $nonce = sanitize_text_field( $_POST['nonce'] );

        if( ! wp_verify_nonce($nonce, 'install-pdf-for-metform') ){
            return;
        }

        $font_json = static::$plugin_dir_path. '/inc/fonts.json'; 
        $fonts = file_get_contents( $font_json );
        $fonts = json_decode( $fonts );

        $upload_dir  = wp_upload_dir();
        $destination = $upload_dir['basedir'].'/pdf4metform/ttfonts/';
        $font        = sanitize_file_name( $_POST['font'] );  

        $res = wp_remote_get(
            $this->font_url . $font,
            [
                'timeout'  => 60,
                'stream'   => true,
                'filename' => $destination . $font,
            ]
        );

        if( end( $fonts ) == $font ){
            $options = get_option('pdf4metform', []);
            $options['installed'] = true;
            update_option('pdf4metform', $options );
        }
        return true;
    }


    public function save_options(){

		if( ! isset($_POST['font']) ) return;

		check_admin_referer( 'pdf-for-metform-options' );
		if ( !current_user_can( 'activate_plugins' ) )  
			wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'pdf-form-metform' ) );

        $font                 = sanitize_text_field( $_POST['font'] );
        $margin_left          = (int) $_POST['margin-left'];
        $margin_right         = (int) $_POST['margin-right'];
        $margin_top           = (int) $_POST['margin-top'];
        $margin_bottom        = (int) $_POST['margin-bottom'];

        $options = get_option('pdf4metform', []);
        
        $options['font']              = $font; 
        $options['margin_left']       = $margin_left; 
        $options['margin_right']      = $margin_right; 
        $options['margin_top']        = $margin_top; 
        $options['margin_bottom']     = $margin_bottom; 

        update_option( 'pdf4metform', $options );
		
    }


}

