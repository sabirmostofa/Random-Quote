<?php
/*
  Plugin Name: WP Random Quote
  Plugin URI: http://www.qotd.org
  Description: Display a random quote provided by QOTD.org in your sidebar as a widget or in a page/post using  shortcode. For more info:<a href="http://www.qotd.org/wp-plugin.html">www.qotd.org/wp-plugin.html</a>
  Version: 1.0
  Author: Sabirul Mostofa
  Author URI: http//:sabirul-mostofa.blogspot.com
  License: GPLv2
 */


/*
  Copyright 2011 Sabirul Mostofa  (email : sabirmostofa@gmail.com)

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

include_once 'widget.php';
if (!class_exists('Random_Quote')):

    class Random_Quote {

        function __construct() {
            //initializing widget
            add_action('widgets_init', create_function('', 'register_widget("Random_Quote_Widget");'));
            add_action('wprq_cron_hook', array($this, 'cron_func'));
            add_shortcode('random-quote', array($this, 'random_quote_shortcode'));
            add_action('admin_menu', array($this, 'CreateMenu'));
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
                    $quote = trim($matches[1]);
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

        // shortcode function
        function random_quote_shortcode($atts= array()) {
            extract(shortcode_atts(array(
                        'font' => '12px'
                            ), $atts));
            $quotes = get_option('wprq_random_quotes');
            if (!$quotes)
                $quotes = $this->cron_func();
            $random_quote = $quotes[rand(0, count($quotes))];

            $r_quote = "<div  style=\"font-size: $font\"> $random_quote</div>";
            return $r_quote;
        }

        //adding menu under Setting main menu
        function CreateMenu() {
            add_options_page('Random Quote', 'Random Quote', 'activate_plugins', 'wpRandomQuote', array($this, 'OptionPage'));
        }

        function OptionPage() {
            ?>
            <div class ="wrap">
                <h3>Available shortcodes for the Random Quote plugin</h3>
                In any post or page add the shotcode <b>[random-quote]</b>    <br/>

                Default font size is 12px. To change the font size add the shortcode like this 
                <b> [random-quote font="16px"] </b> <br/>

                You can find the random quote widget from the widgets section

            </div>
            <?php
        }

    }

//instantiating the plugin class
    $random_quote = new Random_Quote();
endif;
