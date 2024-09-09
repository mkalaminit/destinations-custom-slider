<?php
/*
Plugin Name: Destinations Custom Slider
Plugin URI: https://alamin.tech
Description: Add a Destinations custom post type slider to your website. Use this shortcode: [khj_destinations_slider]
Version: 1.0.0
Author: Mk. Al-Amin
Author URI: https://www.facebook.com/engrmkalamin/
*/

// Enqueue the necessary scripts and styles
function destinations_custom_slider_enqueue_scripts() {
  wp_enqueue_script('jquery');
  wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.min.js', array('jquery'), '1.6.0', true);
  wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/jquery.slick/1.6.0/slick.css', array(), '1.6.0');
  wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '1.6.0');


  // Enqueue custom CSS
  wp_enqueue_style('destinations-slider-css', plugin_dir_url(__FILE__) . 'destinations-slider.css');
}
add_action('wp_enqueue_scripts', 'destinations_custom_slider_enqueue_scripts');

// Register the "Destinations" Custom Post Type
function create_destinations_cpt() {
    $labels = array(
        'name' => _x('Destinations', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Destination', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => __('Destinations', 'textdomain'),
        'name_admin_bar' => __('Destination', 'textdomain'),
        'archives' => __('Destination Archives', 'textdomain'),
        'attributes' => __('Destination Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Destination:', 'textdomain'),
        'all_items' => __('All Destinations', 'textdomain'),
        'add_new_item' => __('Add New Destination', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Destination', 'textdomain'),
        'edit_item' => __('Edit Destination', 'textdomain'),
        'update_item' => __('Update Destination', 'textdomain'),
        'view_item' => __('View Destination', 'textdomain'),
        'view_items' => __('View Destinations', 'textdomain'),
        'search_items' => __('Search Destination', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into Destination', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this Destination', 'textdomain'),
        'items_list' => __('Destinations list', 'textdomain'),
        'items_list_navigation' => __('Destinations list navigation', 'textdomain'),
        'filter_items_list' => __('Filter Destinations list', 'textdomain'),
    );
    $args = array(
        'label' => __('Destination', 'textdomain'),
        'description' => __('Post Type for Destinations', 'textdomain'),
        'labels' => $labels,
        'supports' => array('title', 'thumbnail'), // Only title and featured image
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
    );
    register_post_type('destinations', $args);
}
add_action('init', 'create_destinations_cpt', 0);

// Add URL Meta Field to Destinations CPT
function destinations_add_meta_box() {
    add_meta_box(
        'destinations_url_meta_box', // ID of the meta box
        __('Destination URL', 'textdomain'), // Title of the meta box
        'destinations_url_meta_box_callback', // Callback function
        'destinations', // Post type
        'normal', // Context (where the box appears)
        'default' // Priority
    );
}
add_action('add_meta_boxes', 'destinations_add_meta_box');

function destinations_url_meta_box_callback($post) {
    wp_nonce_field('destinations_save_url_data', 'destinations_url_meta_box_nonce');
    $value = get_post_meta($post->ID, '_destination_url', true);
    echo '<label for="destination_url_field">' . __('URL', 'textdomain') . '</label>';
    echo '<input type="url" id="destination_url_field" name="destination_url_field" value="' . esc_attr($value) . '" size="25" />';
}

function destinations_save_url_data($post_id) {
    if (!isset($_POST['destinations_url_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['destinations_url_meta_box_nonce'], 'destinations_save_url_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['destination_url_field'])) {
        return;
    }
    $my_data = sanitize_text_field($_POST['destination_url_field']);
    update_post_meta($post_id, '_destination_url', $my_data);
}
add_action('save_post', 'destinations_save_url_data');


// Create the slider shortcode for Destinations CPT
function khj_destinations_slider($atts) {
  ob_start();

  $args = array(
    'post_type' => 'destinations',
    'posts_per_page' => -1, // Get all destinations
    'post_status' => 'publish',
  );

  $destinations_query = new WP_Query($args);

  if ($destinations_query->have_posts()) {
    $slider_id = 'khj-destinations-slider'; // Slider ID
    ?>
    <div id="<?php echo esc_attr($slider_id); ?>" class="slider">
      <?php while ($destinations_query->have_posts()) : $destinations_query->the_post(); 
        $destination_url = get_post_meta(get_the_ID(), '_destination_url', true);
        $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
        
        if ($featured_image) :
        ?>
          <div class="khj-destinations-item">
            <a href="<?php echo esc_url($destination_url ? $destination_url : get_permalink()); ?>" target="_blank">
              <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="196" height="288">
              <h3 class="title"><?php echo esc_html(get_the_title()); ?></h3>
            </a>
          </div>
        <?php
        endif;
      endwhile;
      ?>
    </div>
    <script>
      jQuery(document).ready(function($) {
        $('#<?php echo esc_js($slider_id); ?>').slick({
          slidesToShow: 6,
          slidesToScroll: 1,
          dots: false,
          arrows: true,
          prevArrow: '<button type="button" class="slick-prev"></button>',
          nextArrow: '<button type="button" class="slick-next"></button>',
          autoplay: true, // Enable auto-slide
          autoplaySpeed: 2000, // Set auto-slide duration (3000 milliseconds)
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 5,
                slidesToScroll: 1,
                infinite: true,
                dots: false
              }
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 1
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 1
              }
            }
          ]
        });
      });
    </script>
    <?php
  } else {
    echo '<p>No Destinations found.</p>';
  }

  wp_reset_postdata();

  return ob_get_clean();
}
add_shortcode('khj_destinations_slider', 'khj_destinations_slider');