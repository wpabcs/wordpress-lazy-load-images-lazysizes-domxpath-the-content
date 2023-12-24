<?php
/**
 * Plugin Name: Lazy load images
 * Plugin URI: https://www.wpabcs.com/
 * Description: Lazy load resources like images with Lazysizes
 * Version: 1.0
 * Author: wpabcs
 */

// Register and enqueue Lazysizes
wp_enqueue_script( 'lazysizes', trailingslashit( get_stylesheet_directory_uri() ) . 'js/lazysizes.min.js', array(), '5.3.2', true );

function wpabcs_lazyload_images( $content ) {
    if( empty( $content ) ) {
        return $content;
    }
    
    // Create new PHP DOM object
    $dom = new \DOMDocument();

    // Load the $content into the DOM object, LIBXML_HTML_NOIMPLIED turns off the automatic adding of html/body elements | LIBXML_HTML_NODEFDTD prevents a default doctype being added when one is not found.
    $content = $dom->loadHTML( $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ); 

    // Create an instance of DOMXpath
    $xpath = new \DOMXpath( $dom );

    // Find me the image element
    $imgs = $xpath->query( "/descendant::img" );

    foreach ( $imgs as $img ) {
        // Remove WordPress default loading attribute
        $img->removeAttribute( 'loading' );
        // Lazy load the image when it's 20px away from viewport
        $img->setAttribute( 'data-expand', '-20' );
        // Set the sizes attribute automatically with Lazysizes
        $img->setAttribute( 'data-sizes', 'auto' );
        $oldsrc = $img->getAttribute( 'src' );
        // Swap out src with data-src
        $img->setAttribute( 'data-src', $oldsrc );
        // Swap out src with a placeholder
        $newsrc = trailingslashit( get_stylesheet_directory_uri() ) . 'images/fallback.gif';
        $img->setAttribute( 'src', $newsrc );
        $oldsrcset = $img->getAttribute( 'srcset' );
        // Swap out srcset responsive images with data-srcset
        $img->setAttribute( 'data-srcset', $oldsrcset );
        // Swap out srcset with a placeholder
        $newsrcset = trailingslashit( get_stylesheet_directory_uri() ) . 'images/fallback.gif';
        $img->setAttribute( 'srcset', $newsrcset );
        $classes = $img->getAttribute( 'class' );
        // Lazysizes classes
        $classes .= " lazyload lazy lazy-hidden";
        // Set and append new Lazysizes classes
        $img->setAttribute( 'class', $classes );
    }

    // Save the updated HTML
    $content = $dom->saveHTML();

    return $content;
}
add_filter( 'the_content', 'wpabcs_lazyload_images' );
?>
