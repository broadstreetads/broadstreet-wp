<?php

/**************************/
/*    Search WIDGET       */
/**************************/


/**
 * Adds bizy_search_widget widget.
 */
class bizy_search_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bizy_search_widget', // Base ID
			__( 'Bizyhood Search Widget', 'bizy' ), // Name
			array( 'description' => __( 'A WIdget to search in the Bizyhood directory', 'bizyhood' ), ) // Args
		);
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
    
    $color_widget_back = ! empty( $instance['color_widget_back'] ) ? $instance['color_widget_back'] : '';
		$color_cta_back = ! empty( $instance['color_cta_back'] ) ? $instance['color_cta_back'] : '';
		$color_cta_font = ! empty( $instance['color_cta_font'] ) ? $instance['color_cta_font'] : '';
		$color_button_back = ! empty( $instance['color_button_back'] ) ? $instance['color_button_back'] : '';
		$color_button_font = ! empty( $instance['color_button_font'] ) ? $instance['color_button_font'] : '';
		$color_label_font = ! empty( $instance['color_label_font'] ) ? $instance['color_label_font'] : '';
		$color_input_back = ! empty( $instance['color_input_back'] ) ? $instance['color_input_back'] : '';
		$color_input_border = ! empty( $instance['color_input_border'] ) ? $instance['color_input_border'] : '';
		$color_input_font = ! empty( $instance['color_input_font'] ) ? $instance['color_input_font'] : '';
    
    $button_style = $input_style = array();
    if ($color_button_back != '') {
      $button_style[] = 'background-color: #'. $color_button_back .';';
    }
    if ($color_button_back != '') {
      $button_style[] = 'color: #'. $color_button_font .';';
    }
    
    if ($color_input_back != '') {
      $input_style[] = 'background-color: #'. $color_input_back .';';
    }
    if ($color_input_font != '') {
      $input_style[] = 'color: #'. $color_input_font .';';
    }
    if ($color_input_border != '') {
      $input_style[] = 'border-color: #'. $color_input_border .';';
    }
    
    
    
    echo '<form method="get" action="'. get_permalink(get_option('Bizyhood_Main_page_ID')) .'" id="bizyhood_search_'. $widget_id .'" class="bizyhood_search '. $instance['layout'] .'">
      <div class="form_wrap search_layout_'. $instance['layout'] .' table_div">
        <div class="tr_div">
          <div class="search_fields search_fields_label td_div" '. ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '') .'>
            <label for="keywords_'. $instance['layout'] .'" '. ($color_label_font != '' ? 'style="color: '. $color_label_font .'"' : '') .'>'. __('Search for: ', 'bizyhood') .'</label>
          </div>
          <div class="search_fields search_fields_input td_div" '. ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '') .'>
            <input class="search_keywords" id="keywords_'. $instance['layout'] .'" name="keywords" value="'. (isset($_GET['keywords']) ? esc_attr($_GET['keywords']) : '') .'" placeholder="'. __('restaurants, pizza, real-estate etc', 'bizyhood') .'"  '. (!empty($input_style) ? 'style="'. implode(' ', $input_style) .'"' : '') .'/>
          </div>
          <div class="search_fields search_fields_submit td_div" '. ($color_widget_back != '' ? 'style="background-color: '. $color_widget_back .'; border-color: '. $color_widget_back .';"' : '') .'>
            <input type="submit" class="button btn bizy_search_submit" value="'. __('Search', 'bizyhood') .'" '. (!empty($button_style) ? 'style="'. implode(' ', $button_style) .'"' : '') .'>
          </div>
          <div class="list_your_business arrow_box td_div" '. ($color_cta_back != '' ? 'style="background-color: '. $color_cta_back .'; border-color: '. $color_cta_back .';"' : '') .'>
            <a href="'. get_permalink(get_option('Bizyhood_Signup_page_ID')) .'" title="'. __('List you business', 'bizyhood') .'" >
              <span class="link_row row1" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .'>'. __(esc_attr($instance['row1']), 'bizyhood') .'</span>
              <span class="link_row row2" '. ($color_cta_font != '' ? 'style="color: '. $color_cta_font .';"' : '') .'>'. __(esc_attr($instance['row2']), 'bizyhood') .'</span>
            </a>
          </div>
        </div>
      </div>
    </form>
    ';
    
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
		$layout = ! empty( $instance['layout'] ) ? $instance['layout'] : 'full';
		$row1 = ! empty( $instance['row1'] ) ? $instance['row1'] : 'List your business';
		$row2 = ! empty( $instance['row2'] ) ? $instance['row2'] : 'Add now, it\'s free';
		$color_widget_back = ! empty( $instance['color_widget_back'] ) ? $instance['color_widget_back'] : '#e2e2e2';
		$color_cta_back = ! empty( $instance['color_cta_back'] ) ? $instance['color_cta_back'] : '#45AAE8';
		$color_cta_font = ! empty( $instance['color_cta_font'] ) ? $instance['color_cta_font'] : '#FFFFFF';
		$color_button_back = ! empty( $instance['color_button_back'] ) ? $instance['color_button_back'] : '#000000';
		$color_button_font = ! empty( $instance['color_button_font'] ) ? $instance['color_button_font'] : '#FFFFFF';
		$color_label_font = ! empty( $instance['color_label_font'] ) ? $instance['color_label_font'] : '#6E7273';
		$color_input_back = ! empty( $instance['color_input_back'] ) ? $instance['color_input_back'] : '#FFFFFF';
		$color_input_border = ! empty( $instance['color_input_border'] ) ? $instance['color_input_border'] : '#AAAAAA';
		$color_input_font = ! empty( $instance['color_input_font'] ) ? $instance['color_input_font'] : '#333333';
    
    $uid = uniqid ();
		?>
		<p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
      <label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Layout:', 'bizyhood' ); ?></label> 
      <select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>">
        <option value="full" <?php echo ($layout == 'full' ? 'selected="selected"': ''); ?>><?php _e( 'Full width', 'bizyhood' ); ?></option>
        <option value="side" <?php echo ($layout == 'side' ? 'selected="selected"': ''); ?>><?php _e( 'Sidebar', 'bizyhood' ); ?></option>
      </select>
		</p>
		<p>
      <label for="<?php echo $this->get_field_id( 'row1' ); ?>"><?php _e( 'Link text header:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'row1' ); ?>" name="<?php echo $this->get_field_name( 'row1' ); ?>" type="text" value="<?php echo esc_attr( $row1 ); ?>">
		</p>
		<p>
      <label for="<?php echo $this->get_field_id( 'row2' ); ?>"><?php _e( 'Link text subheader:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'row2' ); ?>" name="<?php echo $this->get_field_name( 'row2' ); ?>" type="text" value="<?php echo esc_attr( $row2 ); ?>">
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
        <label for="<?php echo $this->get_field_id( 'color_button_back' ); ?>"><?php _e( 'Button Background:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_button_back' ); ?>" name="<?php echo $this->get_field_name( 'color_button_back' ); ?>" type="text" value="<?php echo esc_attr( $color_button_back ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_button_font' ); ?>"><?php _e( 'Button Font:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_button_font' ); ?>" name="<?php echo $this->get_field_name( 'color_button_font' ); ?>" type="text" value="<?php echo esc_attr( $color_button_font ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_label_font' ); ?>"><?php _e( 'Label Font:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_label_font' ); ?>" name="<?php echo $this->get_field_name( 'color_label_font' ); ?>" type="text" value="<?php echo esc_attr( $color_label_font ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_input_back' ); ?>"><?php _e( 'Input Background:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_input_back' ); ?>" name="<?php echo $this->get_field_name( 'color_input_back' ); ?>" type="text" value="<?php echo esc_attr( $color_input_back ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_input_border' ); ?>"><?php _e( 'Input Border:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_input_border' ); ?>" name="<?php echo $this->get_field_name( 'color_input_border' ); ?>" type="text" value="<?php echo esc_attr( $color_input_border ); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id( 'color_input_font' ); ?>"><?php _e( 'Input Font:' ); ?></label> 
        <input class="widefat color-picker colorfield colorfield_<?php echo $uid; ?> jscolor {width:101, padding:0, shadow:false, borderWidth:0, backgroundColor:'transparent', insetColor:'#000'}" id="<?php echo $this->get_field_id( 'color_input_font' ); ?>" name="<?php echo $this->get_field_name( 'color_input_font' ); ?>" type="text" value="<?php echo esc_attr( $color_input_font ); ?>">
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
		$instance['layout'] = ( ! empty( $new_instance['layout'] ) ) ? strip_tags( $new_instance['layout'] ) : '';
		$instance['row1'] = ( ! empty( $new_instance['row1'] ) ) ? strip_tags( $new_instance['row1'] ) : '';
		$instance['row2'] = ( ! empty( $new_instance['row2'] ) ) ? strip_tags( $new_instance['row2'] ) : '';
    
    // colors
    $instance['color_widget_back']  = ( ! empty( $new_instance['color_widget_back'] ) ) ? strip_tags( $new_instance['color_widget_back'] ) : '';
		$instance['color_cta_back']     = ( ! empty( $new_instance['color_cta_back'] ) ) ? strip_tags( $new_instance['color_cta_back'] ) : '';
		$instance['color_cta_font']     = ( ! empty( $new_instance['color_cta_font'] ) ) ? strip_tags( $new_instance['color_cta_font'] ) : '';
		$instance['color_button_back']  = ( ! empty( $new_instance['color_button_back'] ) ) ? strip_tags( $new_instance['color_button_back'] ) : '';
		$instance['color_button_font']  = ( ! empty( $new_instance['color_button_font'] ) ) ? strip_tags( $new_instance['color_button_font'] ) : '';
		$instance['color_label_font']   = ( ! empty( $new_instance['color_label_font'] ) ) ? strip_tags( $new_instance['color_label_font'] ) : '';
		$instance['color_input_back']   = ( ! empty( $new_instance['color_input_back'] ) ) ? strip_tags( $new_instance['color_input_back'] ) : '';
		$instance['color_input_border'] = ( ! empty( $new_instance['color_input_border'] ) ) ? strip_tags( $new_instance['color_input_border'] ) : '';
		$instance['color_input_font']   = ( ! empty( $new_instance['color_input_font'] ) ) ? strip_tags( $new_instance['color_input_font'] ) : '';
    

		return $instance;
	}

} // class bizy_search_widget