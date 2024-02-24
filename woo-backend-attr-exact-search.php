<?php
/*
Plugin Name: WC Backend Attribute Exact Search
Description: Prioritize exact matches when searching for product attribute terms in the backend for terms shorter than 3 characters.
Version: 1.0.0
Plugin URI: https://alexdraghici.dev/
Author: Alexandru Draghici
Author URI: https://alexdraghici.dev/
*/

if (!defined('ABSPATH')) {
    exit;
}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    exit;
}

add_filter('woocommerce_json_search_found_product_attribute_terms', 'custom_exact_match_search', 10, 2);

/**
 * @param $terms
 * @param $taxonomy
 * @return mixed
 */
function custom_exact_match_search($terms, $taxonomy) {
    global $wpdb;

    $search_text = isset($_GET['term']) ? wc_clean(wp_unslash($_GET['term'])) : '';

    if (!empty($search_text) && \strlen($search_text) < 3) {
        $exact_match_query = $wpdb->prepare(
            "SELECT {$wpdb->terms}.term_id, {$wpdb->terms}.name FROM {$wpdb->terms} JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id WHERE {$wpdb->term_taxonomy}.taxonomy = %s AND LOWER({$wpdb->terms}.name) = LOWER(%s)",
            $taxonomy,
            $search_text
        );

        $exact_matches = $wpdb->get_results($exact_match_query);

        if (!empty($exact_matches)) {
            $terms = $exact_matches;
        }
    }

    return $terms;
}

