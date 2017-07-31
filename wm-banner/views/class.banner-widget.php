<?php if ( ! defined('ABSPATH') ) die ( 'No direct script access.' );

/**
 * Add Banner Widget.
 */
add_action( 'widgets_init', function(){
     register_widget( 'Banner_Widget' );
});

/**
 * Create Banner Widget.
 */
class Banner_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'banner_widget', // Base ID
            'Banner', // Name
            array( 'description' => 'Adiciona um recurso de banner no seu site.', ) // Args
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
        $title = apply_filters( 'widget_title', $instance['title'] );
        $banner_id = apply_filters( 'widget_title', $instance['banner'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) )
                echo $args['before_title'] . $title . $args['after_title'];
        ?>
            <div class="carousel slide">
                <div class="carousel-inner">
                    <?php the_banner( $banner_id ); ?>
                    <?php $i = 1; while ( have_banner_pictures() ) : the_banner_picture(); ?>
                        <div class="item <?php if ($i == 1) echo 'active'; ?>">
                            <a href="<?php echo get_banner_picture_link(); ?>">
                                <?php the_banner_picture_file(); ?>
                            </a>
                            <?php if ( get_banner_picture_description() ) : ?>
                                <div class="carousel-caption">
                                    <h4><?php the_banner_picture_description(); ?></h4>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php $i++; endwhile; ?>
                </div>
            </div>
        <?php
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
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
        $banner = isset( $instance[ 'banner' ] ) ? $instance[ 'banner' ] : '';
        ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>">Título:</label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'banner' ); ?>">Banner:</label> 
                <select class="widefat" id="<?php echo $this->get_field_id( 'banner' ); ?>" name="<?php echo $this->get_field_name( 'banner' ); ?>">
                    <option value=""></option>
                    <?php $banners = WM_Banner_Model::find_all(); ?>
                    <?php foreach ( $banners as $b ) : ?>
                        <option value="<?php echo $b->banner_id; ?>" <?php echo $b->banner_id == $banner ? 'selected' : '' ?>><?php echo $b->banner_name; ?> - <?php echo $b->banner_width . 'x' . $b->banner_height; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
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
            $instance['banner'] = ( ! empty( $new_instance['banner'] ) ) ? strip_tags( $new_instance['banner'] ) : '';
            return $instance;
    }

} // class Banner_Widget