<?php
/*
Plugin Name: Generic File Processing Widget
Plugin URI: http://www.github.com/wikitopian/generic-file-processing-widget
Description: The skeletal interface for uploading, modifying, and outputting files.
Version: 1.0
Author: Matt Parrott
Author URI: http://www.swarmstrategies.com/matt
License: LGPL
 */

class GenericFileProcessingWidget {
    private $dir;
    private $settings;

    public function __construct() {
        $this->dir = plugin_dir_url( __FILE__ );

        register_activation_hook( __FILE__, array( &$this, 'activation' ) );
        register_deactivation_hook( __FILE__, array( &$this, 'deactivation' ) );
        register_uninstall_hook( __FILE__, 'gfpw_uninstall' );
        add_action( 'admin_init', array( &$this, 'admin_init' ) );
    }
    public function activation() {
        if( !$this->settings = get_option( 'gfpw' ) ) {
            $this->settings = array();

            $this->settings['activated'] = true;
            $this->settings['widget_title'] = 'Generic File Processing Widget';
        }
    }
    public function deactivation() {
    }
    public function admin_init() {
        if( !isset( $this->settings ) ) {
            $this->settings = get_option( 'gfpw' );
        }
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts_plupload' ) );
        add_action( 'admin_head', array( &$this, 'admin_head_plupload' ) );
        add_action( 'admin_head', array( &$this, 'admin_head_js' ) );
        add_action( 'wp_dashboard_setup', array( &$this, 'wp_dashboard_setup_add_widget' ) );
    }
    public function admin_enqueue_scripts_plupload( $page ) {
        wp_enqueue_script( 'plupload' );
    }
    public function admin_head_plupload() {
        $plupload_init = array(
            'runtimes' => 'html5,silverlight,flash,html4',
            'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
            'container' => 'plupload-upload-ui', // will be adjusted per uploader
            'drop_element' => 'drag-drop-area', // will be adjusted per uploader
            'file_data_name' => 'async-upload', // will be adjusted per uploader
            'multiple_queues' => true,
            'max_file_size' => wp_max_upload_size() . 'b',
            'url' => admin_url( 'admin-ajax.php' ),
            'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
            'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
            'filters' => array( array( 'title' => "Allowed files", 'extensions' => '*' ) ),
            'multipart' => true,
            'urlstream_upload' => true,
            'multi_selection' => false, // will be added per uploader
            // additional post data to send to our ajax hook
            'multipart_params' => array(
                '_ajax_nonce' => "", // will be added per uploader
                'action' => array( &$this, 'upload' ), // the ajax action name
                'xmlId' => 0 // will be added per uploader
            )
        );

        echo '<script type="text/javascript">';
        echo 'var base_plupload_config=' . json_encode( $plupload_init ) . ';';
        echo '</script>';
    }
    public function wp_dashboard_setup_add_widget() {
        wp_add_dashboard_widget(
            'gfpw_widget',
            $this->settings['widget_title'],
            array( &$this, 'widget' )
        );
    }
    public function widget() {
        $id = "gfpw";
        $nonce = wp_create_nonce( $id . 'pluploadan' );

        echo <<<HTML

<label>Upload XML File...</label>
<input type="hidden" name="{$id}" id="{$id}" value="" />
<div class="plupload-upload-uic hide-if-no-js" id="{$id}plupload-upload-ui">
    <input id="{$id}plupload-browse-button" type="button" value="Select File..." class="button" />
    <span class="ajaxnonceplu" id="ajaxnonceplu{$nonce}"></span>
</div>
<div class="filelist"></div>
<div class="clear"></div>

HTML;
    }
    public function upload() {

        // check ajax nonce
        $xmlId = $_POST["xmlId"];
        check_ajax_referer($xmlId . 'pluploadan');

        // handle file upload
        $status = wp_handle_upload($_FILES[$xmlId . 'async-upload'], array('test_form' => true, 'action' => 'plupload_action'));

        // send the uploaded file url in response
        echo $status['url'];
        exit;
    }
    public function admin_head_js() {
        wp_register_script( 'gfpw', $this->dir.'js/generic-file-processing-widget.js' );
        wp_enqueue_script(  'gfpw' );
    }
}

$gfpw = new GenericFileProcessingWidget();

function gfpw_uninstall() {
    delete_option( 'gfpw' );
}

?>
