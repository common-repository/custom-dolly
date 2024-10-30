<?php
/*
* Plugin Name: Custom Dolly
* Description: Hello Dolly but edit/save your own lyrics from the WordPress admin.
* Author: Chris Dann
* Version: 1.0.0
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: custom-dolly

Custom Dolly is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Custom Dolly is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Custom Dolly. If not, see  https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Register the lyrics post type
function customdolly_register_post_types() {

	$args = array(
		'public' => false
	);
	register_post_type( 'lyrics', $args);
}
add_action( 'init', 'customdolly_register_post_types');

// Register custom admin page for the plugin for administrators
function customdolly_admin_menu() {

    add_theme_page(
        __( 'Custom Dolly', 'custom-dolly' ),
        __( 'Custom Dolly', 'custom-dolly' ),
        'manage_options',
        'custom-dolly',
        'customdolly_admin_page_contents'
    );
}
add_action( 'admin_menu', 'customdolly_admin_menu' );

// Create new post with updated lyrics when they are submitted via plugin admin page
function customdolly_update_lyrics(){
    
    wp_verify_nonce('customdolly_lyrics_update', 'customdollynoncefield'); 
    $post_args = array(
        'post_type' => 'lyrics',
        'post_status' => 'publish',
        'post_title' => 'Custom Dolly Data',
        'post_content' => sanitize_textarea_field($_POST['customdollylyrics'])
    );

    wp_insert_post($post_args);
    wp_redirect(admin_url('admin.php?page=custom-dolly'));
    die();
}
add_action('admin_post_customdolly_submit','customdolly_update_lyrics');

// The contents of the admin page - display 
function customdolly_admin_page_contents() {
    ?>
        <h1><?php esc_html_e('Custom Dolly', 'custom-dolly'); ?></h1>
        <p><?php esc_html_e('Custom Dolly is a customisable version of the Hello Dolly plugin which allows you to add your own lyrics from your favourite song, film, speech or anything else.', 'custom-dolly'); ?></p>
        <p><?php esc_html_e('Copy/paste or manually enter your song lyrics or text into the textarea below, then click Update to save your lyrics.', 'custom-dolly'); ?></p>
        <p><?php esc_html_e('Put each lyric on a new line.  Don\'t leave empty lines.', 'custom-dolly'); ?></p>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
            <?php wp_nonce_field('customdolly_lyrics_update', 'customdollynoncefield'); ?>
            <textarea name="customdollylyrics" id="" cols="100" rows="30"><?php echo esc_textarea(customdolly_get_lyrics()); ?></textarea><br>
            <input name='action' type="hidden" value='customdolly_submit'>
            <input type="submit" value="Update" id="dollyupdate">
        </form>
    <?php
}

// Fetch the most recent lyrics post and return it as a string
function customdolly_get_lyrics() {

    $args = array(
        'posts_per_page' => '1',
        'post_type' => 'lyrics'
    );
    $query_posts = get_posts($args);
    
    if ($query_posts) {
        foreach ($query_posts as $post) {
            $the_content = $post->post_content;
        }
    } else {
        $the_content = "Go to Appearance > Custom Dolly to add your custom lyrics";
    }

    wp_reset_postdata();

    return $the_content;
}


// Get random lyric from the latest lyrics post and display it
function customdolly_get_random_lyric() {
    $the_content = customdolly_get_lyrics();
    $lyric_array = preg_split('/\r\n|\r|\n/', $the_content);
    ?> <p id="dolly"><?php
    $random_line = array_rand($lyric_array);
    echo $lyric_array[$random_line];
    ?></p>
    <?php
}
add_action( 'admin_notices', 'customdolly_get_random_lyric' );


// Original CSS from Hello Dolly plugin
add_action( 'admin_head', 'customdolly_css' );
function customdolly_css() {
	echo "
	<style type='text/css'>
	#dolly {
		float: right;
		padding: 5px 10px;
		margin: 0;
		font-size: 12px;
		line-height: 1.6666;
	}
	.rtl #dolly {
		float: left;
	}
	.block-editor-page #dolly {
		display: none;
	}
	@media screen and (max-width: 782px) {
		#dolly,
		.rtl #dolly {
			float: none;
			padding-left: 0;
			padding-right: 0;
		}
    }
    
    #dollyupdate {
        font-size: 1.2rem;
        padding: .5em 1em;
    }
	</style>
	";
}