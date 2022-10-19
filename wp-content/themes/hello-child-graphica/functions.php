<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN HELLO ELEMENTOR
// AUTOGENERADO - NO MODIFICAR:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor','hello-elementor-theme-style' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END HELLO ELEMENTOR


// AGREGAR CUTOM JS/CSS
function add_scripts_proyecto() {
	wp_enqueue_script( 'script_select', get_stylesheet_directory_uri() . '/lib/select.js', array ( 'jquery' ));
	wp_enqueue_script( 'script_form', get_stylesheet_directory_uri() . '/lib/form.js', array ( 'jquery' ));
	wp_enqueue_script( 'script_custom', get_stylesheet_directory_uri() . '/custom.js', array ( 'jquery' ));
	
	// CSS
    wp_enqueue_style( 'style_form',  get_stylesheet_directory_uri() . '/lib/form.css' );
   	wp_enqueue_style( 'style_slick',  get_stylesheet_directory_uri() . '/lib/app.css' );
	
    wp_localize_script( 'script_custom', 'proyecto', [
        'is_mobile' => intval( wp_is_mobile() ),
    ]);
}
add_action( 'wp_enqueue_scripts', 'add_scripts_proyecto' );

// ***********************************************

// ***********************************************
// QUITAR GUTENBERG

add_filter( 'gutenberg_can_edit_post_type', '__return_false' );
add_filter('use_block_editor_for_post_type', '__return_false', 100);


// ***********************************************



// ***********************************************
// KEEP THE ORIGINAL FILE UPLOAD NAME
/**
* @param string        $filename                 Unique file name.
* @param string        $ext                      File extension, eg. ".png".
* @param string        $dir                      Directory path.
* @param callable|null $unique_filename_callback Callback function that generates the unique file name.
*/
add_filter( 'wp_unique_filename', function( $filename, $ext, $dir, $unique_filename_callback ) {
    $FORM_FIELD_FILE_ID = 'cv_postula';
    
    

    static $file_count = 0; //used to get the current file when multiple uploads in a form

    // first make sure its an upload via elementor forms
    // or you can once again only add this filter hook if you know that this is
    // a desired form as in the above example
    if ( false === strpos( $dir, '/elementor/forms' ) ) {
        return $filename;
    }

    // get the original file name
    $original_filename = $_FILES['form_fields'][$FORM_FIELD_FILE_ID][$file_count]['name'];
    
    // Delete the file extension (slugify replace dots with dash)
    $original_filename = str_replace($ext, '', $original_filename);
    
    
    /** Slugify file name, prevent special characters and space **/
    $slug_filename = slugify($original_filename);
    
    // Bump counter for next file
    $file_count++;
    
    /** Validate if file exist, add number of copy after name **/
    $file_duplicated = true;
    $counter = 1;
    
    $temp_name = $slug_filename . $ext;
    while($file_duplicated){
        if(file_exists($dir.'/'.$temp_name)){
            $temp_name = $slug_filename . '-'.$counter.$ext;
            $file_duplicated = true;
            $counter++;
        }else{
            $file_duplicated = false;
        }
    }
    
    $slug_filename = $temp_name;

    // Return your desired file name (original_filename)
    return $slug_filename;
}, 10, 4);


// Slugify function
function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

// ***********************************************


// ***********************************************
// Libro de Reclamaciones - Código único secuencial

add_action( 'elementor_pro/forms/validation', 'project_libro_reclamaciones_codigo_unico', 10, 2 );


function project_libro_reclamaciones_codigo_unico( $record, $handler ) {
    global $wpdb;
    
    // Verificar que se envíe desde el formulario del libro de reclamaciones
    $form_name = $record->get_form_settings( 'form_name' ); 
    if($form_name != 'Libro de reclamaciones'){
        return;
    }
    
    // Buscamos el último lead enviado
    $fv_form = $wpdb->get_results("SELECT id FROM CJf2B6_e_submissions WHERE element_id = 'acdb477' ORDER BY id DESC LIMIT 1");
    
    // Tomamos el código
    $fv_val  = $wpdb->get_results("SELECT value FROM CJf2B6_e_submissions_values WHERE submission_id = ". $fv_form[0]->id ." AND `key` = 'reclamos_id'");
    
    // Si es el primer lead, se crear el primer código de la secuencia, sino, sumamos uno
    if($fv_val){
        $fv_id = (int)preg_replace('/[^0-9]/', '',$fv_val[0]->value);
        
        $reclamo_num = $fv_id + 1;
    }else{
        $reclamo_num = 1;
    }
        
    // Agregamos el código con el id aumentado  
    $reclamo_id = 'LR-'.sprintf("%04d", $reclamo_num);
    
    // Actualizamos el input hidden del id del reclamo
    $record->update_field('reclamos_id', 'value', $reclamo_id);
    $record->update_field('reclamos_id', 'raw_value', $reclamo_id);
}
// ***********************************************