<?php
/*
Plugin Name: The Feed
Plugin URI: https://github.com/cantoute/the-feed
Description: Provides shortcode [the_feed]
Author: Antony GIBBS + students
Version: 0.0.1
Author URI: https://github.com/cantoute
*/

/******
 * [the_feed menu_categories="auto:4|all|1,2,non-classe]" menu_type="list|select" init_cat="auto|all" page_size="20" pager="yes|no" max_page="5" class="the-feed" target="_self"]
 */

defined('ABSPATH') or die('No script kiddies please!');

const TF_PAGE_MAX = 5;
const TF_PAGE_SIZE = 20;
const TF_MENU_CATEGORIES = 'auto:4';
const TF_MENU_TYPE = 'list';

function add_the_feed_scripts()
{
  wp_enqueue_style('the_feed', plugin_dir_url(__FILE__) . 'the-feed.css', array(), '0.1', 'all');
  wp_enqueue_script('the_feed', plugin_dir_url(__FILE__) . 'the-feed.js', array('jquery'), '0.1', true); // load in footer
}
add_action('wp_enqueue_scripts', 'add_the_feed_scripts');

function the_feed_nav(
  $menu_categories = TF_MENU_CATEGORIES,
  $menu_type = TF_MENU_TYPE,
  $init_cat = null
) {
  $menu = '<nav class="the-feed-menu" data-init-cat="' . $init_cat . '" data-menu-type="' . $menu_type . '">';

  $mc = explode(':', $menu_categories);
  $mc_limit = 0; // no limit

  if (
    'auto' === $mc[0]
    && count($mc) > 1
  ) {
    $mc_limit = $mc[1] || $mc_limit;
  }

  switch ($menu_type) {
    case 'select':
      $menu .= the_feed_err('Sorry: menu_type doesn\'t accept \'select\' yet. (Coming soon)');
      break;
    case 'list':
      $menu .= '<ul>';

      $menu .= '</ul>';
      break;
    default:
      $menu .= the_feed_err('Error: menu_type only accepts \'select\' or \'list\'');
  }

  $menu .= '</nav>';

  return $menu;
}

function the_feed_err($msg = 'Error')
{
  return '<div class="the-feed-error">'
    . htmlspecialchars($msg)
    . '</div>';
}

function the_feed_body($current_cat = null)
{
  $body = '';

  return $body;
}

function the_feed_pager(
  $current_cat = null,
  $page_size = TF_PAGE_SIZE,
  $page_max = TF_PAGE_MAX
) {
  $pager = '<nav class="the-feed-pager"'
    . ' data-current-cat="' . $current_cat . '"'
    . ' data-page-size="' . $page_size . '"'
    . ' data-page-max="' . $page_max . '"'
    . '>';
  $pager .= '</nav>';

  return $pager;
}

function the_feed_func($atts)
{
  $a = shortcode_atts(
    array(
      'menu_categories' => TF_MENU_CATEGORIES,
      'menu_type' => TF_MENU_TYPE,
      'page_size' => TF_PAGE_SIZE,
      'class' => 'the-feed',
      'init_cat' => 'auto',
      'page_max' => TF_PAGE_MAX,
      'pager' => 'yes',
    ),
    $atts
  );

  $html = '<div class="' . $a['class'] . '">';

  $init_cat = null;

  switch ($a['init_cat']) {
    case 'auto':
      // if viewing a post, a category/archive we list post of same category
      // else we display last posts
      if (
        (is_single() && 'post' == get_post_type())
        || is_category()
        || is_archive()
      ) {
        $cat = reset(get_the_category()); // gets first element of an array
        $init_cat = $cat->cat_ID;
      }
      break;

    default:
      if (
        empty($a['init_cat'])
        || (is_numeric($a['init_cat'])
          && intval($a['init_cat']) == $a['init_cat'])
      ) {
        $init_cat = empty($a['init_cat']) ? null : $a['init_cat'];
      } else {
        $html .= the_feed_err('Error: init_cat only accepts an integer (cat_ID). TODO: accept slug');
      }
  }

  $html .= '<header>'
    . the_feed_nav($a['menu_categories'], $a['menu_style'], $init_cat)
    . '</header>'
    . '<div>'
    . the_feed_body($init_cat)
    . '</div>';

  if ('yes' === $a['pager']) {
    $html .= '<footer>'
      . the_feed_pager($init_cat, $a['page_size'], $a['page_max'])
      . '</footer>';
  }

  $html .= '</div>';

  return $html;
}
add_shortcode('the_feed', 'the_feed_func');
