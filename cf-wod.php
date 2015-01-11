<?php

/**
 * Plugin Name: CF Wod Posts
 * Description: Post WODs with WordPress
 * Version: 1.3
 * Author: Matt McGivney
 * Author URI: http://antym.com
 * Stable tag: 1.3
 * Tested up to: 4.1
 * License: GPL2
 */
 
 /*  Copyright 2014 Matt McGivney  (email : matt@antym.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	add_action( 'init', 'cf_wod_create_post_type' );
	
	function cf_wod_create_post_type() {
		register_post_type( 'cf_wod',
			array(
				'labels' => array(
					'name' => __( 'WODs' ),
					'singular_name' => __( 'WOD' ),
					'add_new' => 'Add New',
					'add_new_item' => 'Add New WOD',
					'edit' => 'Edit',
					'edit_item' => 'Edit WOD',
					'new_item' => 'New WOD',
					'view' => 'View',
					'view_item' => 'View WOD',
					'search_items' => 'Search WODs',
					'not_found' => 'No WODs found',
					'not_found_in_trash' => 'No WODs found in trash'
				),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'WODs'),
			'menu_icon' => 'dashicons-star-filled',
			'supports' => array('title', 'editor', 'author', 'comments')
			)
		);
		flush_rewrite_rules();
	} 

	/*
	* Adds custom post type to main query so the WODs mix in with other
	* posts (like blog posts).
	*/
	
	add_action( 'pre_get_posts', 'cf_wod_add_my_post_types_to_query' );
	
	function cf_wod_add_my_post_types_to_query( $query ) {
		
		if (get_option('wods_in_main')) {
			
			if ( is_home() && $query->is_main_query() ) {
					$query->set( 'post_type', array( 'post', 'cf_wod' ) );
			}
			return $query;
		}		
	}

	/**
	* Initializes settings by registering the section, fields etc.
	*
	*/

	add_action('admin_init', 'cf_wod_initialize_wod_options');		
	function cf_wod_initialize_wod_options() {
		
		//Add the setting section to the reading page
		add_settings_section( 'wod_settings_id', 'WOD Settings', 'cf_wod_settings_callback', 'reading' );
		
		// Add the field for toggling whether or not WODs show up in the main query.
		add_settings_field( 
	    	'wods_in_main',
			'WODs in main query?',
			'toggle_wods_main_query_callback',
			'reading',
			'wod_settings_id',
			array('Activate this setting to have the WODs display in the main query alongside other posts (like blog posts).')
		);	
		
		// Finally, we register the fields with WordPress
		register_setting(
			'reading',
			'wods_in_main'
		);
	}
	
	//implementing the callback identified in the add_settings_setion(...) call above
	function cf_wod_settings_callback() {
		echo '<p>Select how you would like to display WODs.</p>';
	}
	
	//implementing the callback identified in the add_settings_section(...) call above
	function toggle_wods_main_query_callback($args) {
	
	// Note the ID and the name attribute of the element should match that of the ID in the call to add_settings_field
    $html = '<input type="checkbox" id="wods_in_main" name="wods_in_main" value="1" ' . checked(1, get_option('wods_in_main'), false) . '/>';
     
    // Here, we will take the first argument of the array and add it to a label next to the checkbox
    $html .= '<label for="wods_in_main"> '  . $args[0] . '</label>';
     
    echo $html;	}
    
    //Word Trimmer
	function cf_wod_custom_posts_word_trimmer($string, $count, $ellipsis = FALSE){
	  $words = explode(' ', $string);
	  if (count($words) > $count){
	    array_splice($words, $count);
	    $string = implode(' ', $words);
	    if (is_string($ellipsis)){
	      $string .= $ellipsis;
	    }
	    elseif ($ellipsis){
	      $string .= '&hellip;';
	    }
	  }
	  return $string;
	}

	class WODPostsContent extends WP_Widget {
	
		//"Constructor" for this widget
		//This function declares the widget
	    function WODPostsContent() {
	        //This function call defines how the widget will display in the widget area of the dashboard.
	        parent::WP_Widget('WODPostsContent', 'WODs', array('description' => 'Display a list of WODs in a widget.'));
	    }
	    function widget($args, $instance) {		
	        extract( $args );
	        $title = apply_filters('widget_title', $instance['title']);
			$customtext = $instance['customtext'];
			$postlength = $instance['postlength'];
			$show_image = $instance['show_image'];
			$posttype = 'cf_wod';
			$moretext = $instance['moretext'];
			$postlength = $instance['postlength'];
			$postnum = $instance['postnum'];
			$byorder = $instance['byorder'];
			$sorter = $instance['sorter'];
	       echo $before_widget; 
				 if ( $title )
				 	echo $before_title . $title .$after_title; 
				$loop = new WP_Query( array( 'post_type' => $posttype, 'posts_per_page' => $postnum, 'orderby'=> $byorder ,'order'=> $sorter) ); 
				while ( $loop->have_posts()) {
				$loop->the_post(); 
							$cont = get_the_content();
							$cont = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $cont, ENT_QUOTES, get_option('blog_charset') ) ) ) );
							$cont = cf_wod_custom_posts_word_trimmer ($cont, $postlength, false);
							$cont = esc_html($cont);
						
						
						echo "<h3><a href='";
						echo the_permalink();
						echo "'>"; 
						echo the_title();
						echo "</a>";
						echo "<br>";
						echo the_time('m/d/y');
						echo "</h3>";
						if ( $show_image ){
							if ( has_post_thumbnail() ) : 
							the_post_thumbnail(array( 100, 100 ),array('class' => 'alignleft'));
							endif;
						}
						echo $cont;
						echo "...";
						echo "<a href='";
						echo the_permalink();
						echo "'>";
						if ($moretext) {echo $moretext;}
						echo "</a><br /><br />";
				} 
			echo $after_widget;
	    }
	
	
	    function update($new_instance, $old_instance) {				
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['customtext'] = $new_instance['customtext'];
			$instance['show_image'] = $new_instance['show_image'];
			$instance['posttype'] = $new_instance['posttype'];
			$instance['moretext'] = strip_tags($new_instance['moretext']);
			$instance['postlength'] = strip_tags($new_instance['postlength']);
			$instance['postnum'] = strip_tags($new_instance['postnum']);
			$instance['byorder'] = $new_instance['byorder'];
			$instance['sorter'] = $new_instance['sorter'];
		    return $instance;
	    }
	
		//This function defines the options shown for the plugin once it is added to the sidebar area of the widgets screen.
		
	    function form($instance) {	
	    	//Sets DEFAULTS for WODS			
			$defaults = array( 'title' => 'WODs', 'moretext' => 'more', 'postnum'=> 3, 'postlength'=>15);
			$instance = wp_parse_args( (array) $instance, $defaults ); ?>
	        
	        <p>
	          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
	          </label>
	          
	          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
	        </p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'postnum' ); ?>">Number of WODs:</label>
				<input id="<?php echo $this->get_field_id( 'postnum' ); ?>" name="<?php echo $this->get_field_name( 'postnum' ); ?>" value="<?php echo $instance['postnum']; ?>" style="width:90%;" />
			</p>
					<p>
				<label for="<?php echo $this->get_field_id( 'postlength' ); ?>">Number of characters per WOD:</label>
				<input id="<?php echo $this->get_field_id( 'postlength' ); ?>" name="<?php echo $this->get_field_name( 'postlength' ); ?>" value="<?php echo $instance['postlength']; ?>" style="width:90%;" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'moretext' ); ?>">Read More text:</label>
				<input id="<?php echo $this->get_field_id( 'moretext' ); ?>" name="<?php echo $this->get_field_name( 'moretext' ); ?>" value="<?php echo $instance['moretext']; ?>" style="width:90%;" />
			</p>
			
			<p>
			  <label for="<?php echo $this->get_field_id( 'byorder' ); ?> "><?php _e('Order By:', 'WODPostsContent'); ?></label>
			  <select id="<?php echo $this->get_field_id( 'byorder' ); ?>" name="<?php echo $this->get_field_name( 'byorder' ); ?>">
			    <option value="" <?php if ( $instance['byorder'] == '' ){ echo 'selected="selected"'; }?>>Date Posted</option>      
			    <option value="menu_order" <?php if ( $instance['byorder'] == 'menu_order'){ echo 'selected="selected"'; }?>>Menu Order</option>    
			    <option value="name" <?php if ( $instance['byorder'] == 'name' ){ echo 'selected="selected"'; }?>>Post Title</option>    
			  </select>
			</p>
			
			<p>
			<label for="<?php echo $this->get_field_id( 'sorter' ); ?> "><?php _e('Order:', 'WODPostsContent'); ?></label>
			<select id="<?php echo $this->get_field_id( 'sorter' ); ?>" name="<?php echo $this->get_field_name( 'sorter' ); ?>">      
			<option value="DESC" <?php if ( $instance['sorter'] == 'DESC' ){ echo 'selected="selected"'; }?>>Descending</option>    
			<option value="ASC" <?php if ( $instance['sorter'] == 'ASC' ){ echo 'selected="selected"'; }?>>Ascending</option>
			</select>
			</p>
			
			<p>
				<input class="checkbox" type="checkbox"<?php checked( (bool) $instance['show_image'], true ); ?> id="<?php echo $this->get_field_id( 'show_image' ); ?>" name="<?php echo $this->get_field_name( 'show_image' ); ?>" /> 
				<label for="<?php echo $this->get_field_id( 'show_image' ); ?>"><?php _e('Display Featured Image', 'WODPostsContent'); ?></label>
			</p>
	
	        <?php 
	    }
	
	} // class WODPostsContent

	// register WODPostsContent widget
	add_action('widgets_init', create_function('', 'return register_widget("WODPostsContent");'));

	/*
	* Adds shortcode to allow inclusion of WODs in content areas
	*/
	
	function cf_wods_custom_shortcode( $atts ) {

		// Attributes
		extract( shortcode_atts(
			array(
				'id' => '',
			), $atts )
		);
	
		if ( FALSE === get_post_status( $id ) ) {
  			return "Error: The WOD referenced in your shortcode doesn't exist.";
		} else {
  			$cf_wod_post=get_post($id);
		return $cf_wod_post->post_title . "<br>" 
			. $cf_wod_post->post_content;
		}		
	}

	add_action( 'init', 'cf_wod_add_shortcode');
	function cf_wod_add_shortcode () {
		add_shortcode( 'cf_wods', 'cf_wods_custom_shortcode' );	
	}
	
?>