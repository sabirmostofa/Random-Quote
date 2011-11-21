<?php

/*
  Plugin Name: WP-Random-Quote
  Plugin URI: http://sabirul-mostofa.blogspot.com
  Description: Display Random quotes in your sidebar or in any page using shortcode
  Version: 1.0
  Author: Sabirul Mostofa
  Author URI: http://sabirul-mostofa.blogspot.com
 */
include_once 'widget.php';
if (!class_exists('Random_Quote')):

    class Random_Quote {

        function __construct() {
            //initializing widget
            add_action('widgets_init', create_function('', 'register_widget("Random_Quote_Widget");'));
            add_action('wprq_cron_hook', array($this, 'cron_func'));
        }

        function cron_func() {
            // updating the quotes
            $prev = get_option('wprq_random_quotes');
            if (!$prev)
                $prev = array();
            $quotes = array();
            $count = 0;

            for ($i = 0; $i < 25; $i++):
                $html = wp_remote_get('http://wpquote.qotd.org/');
                if (isset($html['body'])):
                    preg_match('/<body>(.*)<\/body>/s', $html['body'], $matches);
                    $quote = trim($matches[0]);
                    if (!in_array($quote, $quotes) && !in_array($quote, $prev)) {
                        $quotes[] = $quote;
                        if (++$count == 10)
                            break;
                    }
                endif;
            endfor;
            update_option('wprq_random_quotes', $quotes);
            return $quotes;
        }

        //adding or changing the cron events
        function add_change_cron($sched) {
            if (wp_get_schedule('wprq_cron_hook') != $sched) {
                wp_clear_scheduled_hook('wprq_cron_hook');
                wp_schedule_event(time(), $sched, 'wprq_cron_hook');
            }
        }

    }

//instantiating the plugin class
    $random_quote = new Random_Quote();
endif;
