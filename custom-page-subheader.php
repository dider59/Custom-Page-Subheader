<?php
/*
Plugin Name: Custom Page Subheader
Description: Add custom page subheader with title, background image, and breadcrumbs.
Version: 1.1
Author: Dider.Dev
Author URI: https://dider.dev
*/

// Register plugin settings
function custom_page_header_register_settings() {
    add_option( 'custom_page_header_location', 'get_header' );
    register_setting( 'custom_page_header_options', 'custom_page_header_location' );
}
add_action( 'admin_init', 'custom_page_header_register_settings' );

// Add plugin settings page
function custom_page_header_settings_page() {
    ?>
    <div class="wrap">
        <h2>Custom Page Subheader</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'custom_page_header_options' ); ?>
            <?php do_settings_sections( 'custom_page_header_options' ); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Custom Page Subheader Location (hook)</th>
                    <td>
                        <input type="text" name="custom_page_header_location" value="<?php echo esc_attr( get_option( 'custom_page_header_location' ) ); ?>" />
                        <p class="description">Enter the hook where you want the custom page header to appear (e.g., 'wp_head').</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add plugin settings link to the admin menu
function custom_page_header_settings_link() {
    add_options_page( 'Custom Page Header Settings', 'Custom Page Subheader', 'manage_options', 'custom-page-header-settings', 'custom_page_header_settings_page' );
}
add_action( 'admin_menu', 'custom_page_header_settings_link' );

// Function to display custom page header
function custom_page_header() {

	// Check if custom page header is enabled for this page
    $hide_header = get_post_meta( get_the_ID(), '_custom_page_header_hide', true );
    if ( $hide_header ) {
        return; // Don't display header if it's hidden for this page
    }
	
	global $post;

    // Get the page title
    $title = '';

    if ( is_singular() ) {
        $title = get_the_title();
    } elseif ( is_archive() ) {
        $archive_title = get_the_archive_title();
        $title = strip_tags( $archive_title );
    } elseif ( is_home() ) {
        // Get the ID of the page set as the posts page
        $posts_page_id = get_option( 'page_for_posts' );

        if ( $posts_page_id ) {
            $title = get_the_title( $posts_page_id );
            // Get the featured image URL for the blog page
            $background_image = get_the_post_thumbnail_url( $posts_page_id, 'full' );
        }
    }

    // Check if we have a valid title
    if ( ! $title ) {
        return;
    }

    // Get the featured image URL
    if ( empty( $background_image ) && has_post_thumbnail( $post ) ) {
        $background_image = get_the_post_thumbnail_url( $post, 'full' );
    }

    // Output custom page header HTML
    
	echo '<div class="custom-page-header" style="background-image: url(' . esc_url( $background_image ) . ');"> <div class="container"> <h1 class="page-title">' . esc_html( $title ) . '</h1> </div> </div>';
	
	?>
	<style>
	.custom-page-header{
		position:relative;
		padding: 120px 0;
		background-position:center;
		background-size:cover;
		text-align:center;
		color:#fff;
		text-transform: uppercase;
		font-weight: bold;
	}
	.custom-page-header .page-title{
		margin: 0;
	}
	.custom-page-header::before{
		content:'';
		position:absolute;
		top:0;
		left:0;
		width:100%;
		height:100%;
		background-color:rgba(0,0,0,.5)
	}
	</style>

	<?php
	// Output custom page header HTML
}

function custom_page_header_display() {
    $location = get_option( 'custom_page_header_location', 'get_header' );
    add_action( $location, 'custom_page_header_callback' );
}

function custom_page_header_callback() {
    if ( !is_single() ) {
        custom_page_header();
    }
}
add_action( 'init', 'custom_page_header_display' );

// Add custom field for hiding the page header on individual pages
function custom_page_header_meta_box() {
    add_meta_box(
        'custom_page_header_hide',
        'Hide Custom Page Header',
        'custom_page_header_meta_box_callback',
        'page',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'custom_page_header_meta_box' );

// Callback function to display the custom field
function custom_page_header_meta_box_callback( $post ) {
    $value = get_post_meta( $post->ID, '_custom_page_header_hide', true );
    ?>
    <label for="custom_page_header_hide">
        <input type="checkbox" name="custom_page_header_hide" id="custom_page_header_hide" <?php checked( $value, 'on' ); ?>>
        Hide custom page Subheader on this page
    </label>
    <?php
}

// Save custom field data
function custom_page_header_save_meta_box_data( $post_id ) {
    if ( isset( $_POST['custom_page_header_hide'] ) ) {
        update_post_meta( $post_id, '_custom_page_header_hide', 'on' );
    } else {
        delete_post_meta( $post_id, '_custom_page_header_hide' );
    }
}
add_action( 'save_post_page', 'custom_page_header_save_meta_box_data' );
