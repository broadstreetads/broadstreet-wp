<?php

/**********************/
/* EVENTS  WIDGET */
/**********************/


/**
 * Adds bizy_events_widget widget.
 */
class bizy_events_widget extends WP_Widget {
  
  var $limitchars = 40;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bizy_events_widget', // Base ID
			__( 'Bizyhood Events', 'bizy' ), // Name
			array( 'description' => __( 'A Widget to display bizyhood events', 'bizyhood' ), ) // Args
		);
    
    add_action('admin_enqueue_scripts', array($this, 'upload_scripts'));
	}
  
   /**
     * Upload the Javascripts for the media uploader
     */
  public function upload_scripts() {
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
      wp_enqueue_script('upload_media_widget', Bizyhood_Utility::getJSBaseURL().'bizyhood-upload-media.js', array('jquery'));

      wp_enqueue_style('thickbox');
  }

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

    $widget_id = $args['widget_id'];
    
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
    
    $atts = array();
        
    // cache the results
    $events = Bizyhood_Core::get_cache_value('bizyhood_events_widget', 'response_json', 'business_details_information', $atts, 'events', false);
    
    // if no events are found exit with an error message
    if (empty($events) || $events === false || $events === NULL) {
      echo __('There are no events to display', 'bizyhood');
      echo $args['after_widget'];
      return;
    }
    
    // get random weighted event
    $event = $events[Bizyhood_Utility::getRandomWeightedElementByDate($events, 'end')];

    $view_business_page_id = Bizyhood_Utility::getOption(Bizyhood_Core::KEY_OVERVIEW_PAGE_ID);
    
    
    // get date text
    $dates = Bizyhood_Utility::buildDateText($event['start'], $event['end'], 'Event', 'events');
    
   
    if (empty($event['business_logo'])) {
      
     // set the default
      $event['business_logo'] = Bizyhood_Utility::getDefaultLogo();
      
      // check if a custom is set via the widget
      $headers = @get_headers($instance['image']);
      
      if (isset($instance['image']) && $instance['image'] != '' && !($headers[0] == 'HTTP/1.1 404 Not Found')) {
        $img = getimagesize($instance['image']);
        
        if (is_array($img)) {
        
          $event['business_logo']['image']['url'] = $instance['image'];
          $event['business_logo']['image_width'] = $img[0];
          $event['business_logo']['image_height'] = $img[1];
        
        } 
      }
    }
    
    
    $intro = ! empty( $instance['intro'] ) ? trim($instance['intro']) : '';
    
    $color_widget_back = ! empty( $instance['color_widget_back'] ) ? $instance['color_widget_back'] : '';
		$color_cta_back = ! empty( $instance['color_cta_back'] ) ? $instance['color_cta_back'] : '';
		$color_cta_font = ! empty( $instance['color_cta_font'] ) ? $instance['color_cta_font'] : '';	
    $color_label_font = ! empty( $instance['color_label_font'] ) ? $instance['color_label_font'] : '';
    $color_event_font = ! empty( $instance['color_event_font'] ) ? $instance['color_event_font'] : '';
    $events_page = ! empty( $instance['events_page'] ) ? $instance['events_page'] : '';
    $logo_size = ! empty( $instance['logo_size'] ) ? $instance['logo_size'] : 'large';
    
		
    $widget_backcolor = ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '');
    
    echo '<div id="bizyhood_events_'. $widget_id .'" class="bizyhood_widget bizyhood_events '. (!empty($event->business_logo) ? 'has_logo' : '') .' '. $instance['layout'] .'">';
    
    echo '
    <div class="wrap widget_layout_'. $instance['layout'] .' table_div">
      <div class="tr_div" '. $widget_backcolor .'>';

      if ($intro != '') {
      ?>
        <div class="events_fields events_intro td_div" <?php echo $widget_backcolor; ?>>
          <div <?php echo ($color_label_font != '' ? 'style="color: '. $color_label_font .'"' : ''); ?>>
            <?php echo substr($intro, 0, $this->limitchars); ?>
          </div>
        </div>
      <?php
      }
      
      if ($logo_size != 'hide' ) {
      ?>
      <div class="events_fields  events_logo td_div" <?php echo $widget_backcolor; ?>>
        <a href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $event['business_slug'].'/'.$event['business_identifier']; ?>/" title="<?php echo $event['business_name'] .' '. __('events', 'bizyhood'); ?>">
          <img alt="<?php echo $event['name']; ?>" src="<?php echo $event['business_logo']['image']['url']; ?>" width="<?php echo $event['business_logo']['image_width']; ?>" height="<?php echo $event['business_logo']['image_height']; ?>" class="<?php echo $logo_size;?>" />
        </a>
      </div>
      
      <?php } ?>
      
      <!-- business info START -->
      <div class="events_fields  events_info td_div" <?php echo $widget_backcolor; ?>>
        <a <?php echo ($color_event_font != '' ? 'style="color: '. $color_event_font .'"' : ''); ?> title="<?php echo htmlentities($event['business_name']); ?>" href="<?php echo get_permalink( $view_business_page_id ); ?><?php echo $event['business_slug'].'/'.$event['business_identifier']; ?>/">
          <span class="business_name"><?php echo $event['business_name']; ?></span>
        </a>
        <span class="event_name">
          <a title="<?php echo $event['name']; ?>" href="<?php echo get_permalink( $view_business_page_id ).$event['business_slug'].'/'.$event['business_identifier']; ?>/" <?php echo ($color_event_font != '' ? 'style="color: '. $color_event_font .'"' : ''); ?>>
            <?php echo $event['name']; ?>
          </a>
        </span>
        <?php
          // trim the description if needed
          if (str_word_count($event['description']) > Bizyhood_Core::EXCERPT_MAX_LENGTH) {
            $event['description'] = wp_trim_words($event['description'], Bizyhood_Core::EXCERPT_MAX_LENGTH, ' <a href="'. get_permalink( $view_business_page_id ).$event['business_slug'].'/'.$event['business_identifier'] .'/" title="'. $event['business_name'] .' '. __('events', 'bizyhood').'">more&hellip;</a>');
          }
        ?>
        
        <span class="event_description" <?php echo ($color_event_font != '' ? 'style="color: '. $color_event_font .'"' : ''); ?>><?php echo $event['description']; ?></span>
        <?php echo $dates; ?>
      </div>
      <!-- business info END -->
      <?php
      echo '
        <div class="events_fields list_your_business arrow_box td_div" '. ($color_cta_back != '' ? 'style="background-color: '. $color_cta_back .'; border-color: '. $color_cta_back .';"' : '') .'>
          
            <a href="'. $events_page .'" title="'. __('All events', 'bizyhood') .'" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .' >
              <span class="link_row row1" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .'>
                '. __(esc_attr(substr($instance['row1'], 0, $this->limitchars)), 'bizyhood') .'
              </span>
            </a>
            <a href="'. $events_page .'" title="'. __('All events', 'bizyhood') .'" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .' >
              <span class="link_row row2" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .'>
                '. __(esc_attr(substr($instance['row2'], 0, $this->limitchars)), 'bizyhood') .'
              </span>
            </a>
        </div>
      </div>
    </div>
  ';

    
    echo '</div>';
    
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'bizyhood' );
		$events_page = ! empty( $instance['events_page'] ) ? $instance['events_page'] : '';
		$layout = ! empty( $instance['layout'] ) ? $instance['layout'] : 'full';
		$intro = ! empty( $instance['intro'] ) ? $instance['intro'] : '';
		$row1 = ! empty( $instance['row1'] ) ? $instance['row1'] : 'Want to see all our local events?';
		$row2 = ! empty( $instance['row2'] ) ? $instance['row2'] : 'Click Here';
		$color_widget_back = ! empty( $instance['color_widget_back'] ) ? $instance['color_widget_back'] : '#e2e2e2';
		$color_cta_back = ! empty( $instance['color_cta_back'] ) ? $instance['color_cta_back'] : '#45AAE8';
		$color_cta_font = ! empty( $instance['color_cta_font'] ) ? $instance['color_cta_font'] : '#FFFFFF';
		$color_label_font = ! empty( $instance['color_label_font'] ) ? $instance['color_label_font'] : '#6E7273';
		$color_event_font = ! empty( $instance['color_event_font'] ) ? $instance['color_event_font'] : '#333333';
		$image = ! empty( $instance['image'] ) ? $instance['image'] : '';
		$logo_size = ! empty( $instance['logo_size'] ) ? $instance['logo_size'] : 'large';
    
    $uid = uniqid ();
		?>
		<p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
    <p>
      <label for="<?php echo $this->get_field_id( 'events_page' ); ?>"><?php _e( 'Events page to link to:' ); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id( 'events_page' ); ?>" name="<?php echo $this->get_field_name( 'events_page' ); ?>" type="text" value="<?php echo esc_attr( $events_page ); ?>"><br />
      <small>Enter a fully qualified URL</small>
    </p>
		<p>
      <label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Layout:', 'bizyhood' ); ?></label> 
      <select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>">
        <option value="full" <?php echo ($layout == 'full' ? 'selected="selected"': ''); ?>><?php _e( 'Full width', 'bizyhood' ); ?></option>
        <option value="side" <?php echo ($layout == 'side' ? 'selected="selected"': ''); ?>><?php _e( 'Sidebar', 'bizyhood' ); ?></option>
      </select>
		</p>
		
    
    <p>
        <label for="<?php echo $this->get_field_name( 'image' ); ?>"><?php _e( 'Custom Logo:' ); ?></label>
        <input name="<?php echo $this->get_field_name( 'image' ); ?>" id="<?php echo $this->get_field_id( 'image' ); ?>" class="widefat" type="text" size="36"  value="<?php echo esc_url( $image ); ?>" />
        <input class="upload_image_button button button-primary" type="button" value="Upload Image" />
    </p>
    
    <p>
      <label for="<?php echo $this->get_field_id( 'logo_size' ); ?>"><?php _e( 'Logo Width:', 'bizyhood' ); ?></label> 
      <select class="widefat" id="<?php echo $this->get_field_id( 'logo_size' ); ?>" name="<?php echo $this->get_field_name( 'logo_size' ); ?>">
        <option value="large" <?php echo ($logo_size == 'large' ? 'selected="selected"': ''); ?>><?php echo __('large', 'bizyhood'); ?></option>
        <option value="small" <?php echo ($logo_size == 'small' ? 'selected="selected"': ''); ?>><?php echo __('small', 'bizyhood'); ?></option>
        <option value="hide" <?php echo ($logo_size == 'hide' ? 'selected="selected"': ''); ?>><?php echo __('hide', 'bizyhood'); ?></option>
      </select>
		</p>
    
		<p>
      <label for="<?php echo $this->get_field_id( 'intro' ); ?>"><?php _e( 'Intro text:' ); ?></label> 
      <input placeholder="eg. Our Local Businesses events" class="widefat" maxlength="30" id="<?php echo $this->get_field_id( 'intro' ); ?>" name="<?php echo $this->get_field_name( 'intro' ); ?>" type="text" value="<?php echo esc_attr( $intro ); ?>">
      <small><?php echo $this->limitchars .' '. __('characters max', 'bizyhood' ); ?></small>
		</p>
		<p>
      <label for="<?php echo $this->get_field_id( 'row1' ); ?>"><?php _e( 'Link text header:' ); ?></label> 
      <input class="widefat" maxlength="30" id="<?php echo $this->get_field_id( 'row1' ); ?>" name="<?php echo $this->get_field_name( 'row1' ); ?>" type="text" value="<?php echo esc_attr( $row1 ); ?>">
      <small><?php echo $this->limitchars .' '. __('characters max', 'bizyhood' ); ?></small>
    </p>
		<p>
      <label for="<?php echo $this->get_field_id( 'row2' ); ?>"><?php _e( 'Link text subheader:' ); ?></label> 
      <input class="widefat" maxlength="30" id="<?php echo $this->get_field_id( 'row2' ); ?>" name="<?php echo $this->get_field_name( 'row2' ); ?>" type="text" value="<?php echo esc_attr( $row2 ); ?>">
      <small><?php echo $this->limitchars .' '. __('characters max', 'bizyhood' ); ?></small>
    </p>

    <h4>Colors</h4>
    <div class="color_wrap color_wrap_<?php echo $uid; ?>">
      <p>
        <label for="<?php echo $this->get_field_id( 'color_widget_back' ); ?>"><?php _e( 'Widget Background:' ); ?></label> 
        <input class="widefat color-picker colorfield jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_widget_back' ); ?>" name="<?php echo $this->get_field_name( 'color_widget_back' ); ?>" type="text" value="<?php echo esc_attr( $color_widget_back ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_cta_back' ); ?>"><?php _e( 'Call to Action Background:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_cta_back' ); ?>" name="<?php echo $this->get_field_name( 'color_cta_back' ); ?>" type="text" value="<?php echo esc_attr( $color_cta_back ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_cta_font' ); ?>"><?php _e( 'Call to Action Font:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_cta_font' ); ?>" name="<?php echo $this->get_field_name( 'color_cta_font' ); ?>" type="text" value="<?php echo esc_attr( $color_cta_font ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_label_font' ); ?>"><?php _e( 'Label Font:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_label_font' ); ?>" name="<?php echo $this->get_field_name( 'color_label_font' ); ?>" type="text" value="<?php echo esc_attr( $color_label_font ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_event_font' ); ?>"><?php _e( 'event Info Font:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_event_font' ); ?>" name="<?php echo $this->get_field_name( 'color_event_font' ); ?>" type="text" value="<?php echo esc_attr( $color_event_font ); ?>">
      </p>
      <p>
        <a class="colorfield_reset" href="#">Reset Colors to Default</a>
      </p>
    </div>
    <script>
      jQuery(document).ready(function() {
        
        jQuery('#widgets-right .color-picker, .inactive-sidebar .color-picker').wpColorPicker();
        jQuery(document).ajaxComplete(function() {
          jQuery('#widgets-right .color-picker, .inactive-sidebar .color-picker').wpColorPicker();
        });
        
        jQuery('.colorfield_reset').on('click', function(e) {
          e.preventDefault();
          jQuery('#widgets-right .color-picker, .inactive-sidebar .color-picker').wpColorPicker('color', false);
          jQuery('#widgets-right .color-picker, .inactive-sidebar .color-picker').wpColorPicker('color', '');
          jQuery('.color_wrap_<?php echo $uid; ?> .wp-color-result').css({'background-color':'rgba(0,0,0,0)'});
          jQuery(this).closest('div').find('input.colorfield').val('');
          return false;
          
        });
      });
    </script>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['events_page'] = ( ! empty( $new_instance['events_page'] ) ) ? strip_tags( $new_instance['events_page'] ) : '';
		$instance['layout'] = ( ! empty( $new_instance['layout'] ) ) ? strip_tags( $new_instance['layout'] ) : '';
		$instance['intro'] = ( ! empty( $new_instance['intro'] ) ) ? strip_tags( $new_instance['intro'] ) : '';
		$instance['row1'] = ( ! empty( $new_instance['row1'] ) ) ? strip_tags( $new_instance['row1'] ) : '';
		$instance['row2'] = ( ! empty( $new_instance['row2'] ) ) ? strip_tags( $new_instance['row2'] ) : '';
    
    
    // colors
    $instance['color_widget_back']    = ( ! empty( $new_instance['color_widget_back'] ) ) ? strip_tags( $new_instance['color_widget_back'] ) : '';
		$instance['color_cta_back']       = ( ! empty( $new_instance['color_cta_back'] ) ) ? strip_tags( $new_instance['color_cta_back'] ) : '';
		$instance['color_cta_font']       = ( ! empty( $new_instance['color_cta_font'] ) ) ? strip_tags( $new_instance['color_cta_font'] ) : '';
		$instance['color_label_font']     = ( ! empty( $new_instance['color_label_font'] ) ) ? strip_tags( $new_instance['color_label_font'] ) : '';   
		$instance['color_event_font']     = ( ! empty( $new_instance['color_event_font'] ) ) ? strip_tags( $new_instance['color_event_font'] ) : '';
    
    // image
		$instance['image']            = ( ! empty( $new_instance['image'] ) ) ? strip_tags( $new_instance['image'] ) : '';
		$instance['logo_size']        = ( ! empty( $new_instance['logo_size'] ) ) ? strip_tags( $new_instance['logo_size'] ) : ''; 


		return $instance;
	}

} // class bizy_events_widget