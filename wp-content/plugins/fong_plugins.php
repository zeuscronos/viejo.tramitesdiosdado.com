<?php
/*
Plugin Name: Funciones
Plugin URI: https://ayudawp.com/
Description: Plugin para liberar de funciones el fichero <code>functions.php</code> y activarlo a placer (o no) .
Version: 1.0
Author: Fernando Tellado
Author URI: https://tellado.es
License: GPLv2 o posterior
*/

function cambiar_img_por_figure( $content )
{ 
    $content = preg_replace( 
        '/<p>\\s*?(<a rel=\"attachment.*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', 
        '<figure>$1</figure>', 
        $content 
    ); 
    return $content; 
} 
add_filter( 'the_content', 'cambiar_img_por_figure', 99 );

add_filter ('comment_form_field_url', function ($url) {
  return;
});