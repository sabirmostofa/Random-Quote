<?php

class Random_Quote_Widget extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'randomquote', 'description' => 'Display a random quote');
        $control_ops = array('id_base' => 'random-quotes');
        $this->WP_Widget('random-quotes', 'Random Quotes', $widget_ops, $control_ops);
    }

    function form($instance) {
        // outputs the options form on admin
        $defs = array(
            'title' => '',
            'interval' => 'hourly'
        );
        $instance = wp_parse_args((array) $instance, $defs);
        ?>
        <!--Title -->
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title'; ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($instance['title']); ?>" class="widefat" /></p>

        <!-- Check Interval -->
        <p><label for="<?php echo $this->get_field_id('interval'); ?>"><?php echo 'Interval between fetching the new quotes(In minutes)'; ?>:</label>
            <select  id="<?php echo $this->get_field_id('interval'); ?>" name="<?php echo $this->get_field_name('interval'); ?>" >
                <option <?php selected($instance['interval'], 'hourly'); ?> value="hourly">Hourly</option>
                <option <?php selected($instance['interval'], 'twicedaily'); ?> value="twicedaily" >Twice Daily</option>
                <option <?php selected($instance['interval'], 'daily'); ?> value="daily" >Daily</option>

            </select>

            <?php
        }

        function update($new_instance, $old_instance) {
            // processes widget options to be saved  
            if ($new_instance['interval'] != $old_instance['interval']) {
                global $random_quote;
                $random_quote->add_change_cron($new_instance['interval']);
            }
       
            return $new_instance;
        }

        function widget($args, $instance) {
            global $random_quote;
            // outputs the content of the widget
            $quotes = get_option('wprq_random_quotes');
            if(!$quotes)
                $quotes = $random_quote ->cron_func();
            $random_quote = $quotes[rand(0, count($quotes))];
            ?>
             <div id="wp_random_quote"><?php echo $random_quote ?></div>
            <?php
        }

    }

    