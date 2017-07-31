<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * Widget Receita class
 */
class WP_Widget_Receita extends WP_Widget {
    
    public static function init() {
        /*
         * Register receita widgets
         */
        register_widget( 'WP_Widget_Receita_Categories' );
    }
    
}

/**
 * Widget Receita Categories class
 *
 * @since 2.8.0
 */
class WP_Widget_Receita_Categories extends WP_Widget {

    public function __construct() {
        $widget_ops = array( 'classname' => 'widget_cat_receitas', 'description' => __( "A list or dropdown of categories." ) );
        parent::__construct('cat_receitas', __('Categoria de Receitas'), $widget_ops);
    }

    public function widget( $args, $instance ) {

        /** This filter is documented in wp-includes/default-widgets.php */
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base );
        
        $exclude_cat = $instance['exclude_cat'];

        $c = ! empty( $instance['count'] ) ? '1' : '0';
        $h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
        $d = ! empty( $instance['dropdown'] ) ? '1' : '0';

        echo $args['before_widget'];
        
        if ( $title ) {
                echo $args['before_title'] . $title . $args['after_title'];
        }

        $cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'cat_receitas', 'exclude' => $exclude_cat);

        if ( $d ) {
            $cat_args['show_option_none'] = __('Select Category');
            /**
             * Filter the arguments for the Categories widget drop-down.
             *
             * @since 2.8.0
             *
             * @see wp_dropdown_categories()
             *
             * @param array $cat_args An array of Categories widget drop-down arguments.
             */
            wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args ) );
            ?>
                <script type='text/javascript'>
                /* <![CDATA[ */
                        var dropdown = document.getElementById("cat");
                        function onCatChange() {
                                if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
                                        location.href = "<?php echo home_url(); ?>/?cat_receitas="+dropdown.options[dropdown.selectedIndex].value;
                                }
                        }
                        dropdown.onchange = onCatChange;
                /* ]]> */
                </script>
            <?php
                } else {
            ?>
                <ul>
            <?php
                $cat_args['title_li'] = '';

                /**
                 * Filter the arguments for the Categories widget.
                 *
                 * @since 2.8.0
                 *
                 * @param array $cat_args An array of Categories widget options.
                 */
                wp_list_categories( apply_filters( 'widget_cat_receitas_args', $cat_args ) );
            ?>
                </ul>
            <?php
        }
        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['exclude_cat'] = strip_tags($new_instance['exclude_cat']);
        $instance['count'] = !empty($new_instance['count']) ? 1 : 0;
        $instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
        $instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

        return $instance;
    }

    public function form( $instance ) {
        //Defaults
        $instance = wp_parse_args( (array) $instance, array( 'title' => '') );
        $title = esc_attr( $instance['title'] );
        $exclude_cat = isset( $instance['exclude_cat'] ) ? esc_attr( $instance['exclude_cat'] ) : '';
        $count = isset($instance['count']) ? (bool) $instance['count'] :false;
        $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
        $dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
            
            <p><label for="<?php echo $this->get_field_id('exclude_cat'); ?>"><?php _e( 'Exclude:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('exclude_cat'); ?>" name="<?php echo $this->get_field_name('exclude_cat'); ?>" type="text" value="<?php echo $exclude_cat; ?>" /></p>

            <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
            <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
            <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
            <label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
        <?php
    }

}