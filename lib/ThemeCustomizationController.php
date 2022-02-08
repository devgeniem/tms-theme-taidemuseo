<?php
/**
 * Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo;

use WP_post;

/**
 * Class ThemeCustomizationController
 *
 * @package TMS\Theme\Base
 */
class ThemeCustomizationController implements \TMS\Theme\Base\Interfaces\Controller {

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_filter(
            'tms/single/related_display_categories',
            '__return_false',
        );

        add_filter( 'tms/theme/nav_parent_link_is_trigger_only', '__return_true' );

        add_filter( 'tms/theme/header/colors', [ $this, 'header' ] );
        add_filter( 'tms/theme/footer/colors', [ $this, 'footer' ] );
        add_filter( 'tms/theme/search/search_item', [ $this, 'search_classes' ] );
    }

    /**
     * Header
     *
     * @param array $colors Color classes.
     *
     * @return array Array of customized colors.
     */
    public function header( $colors ) : array {
        $colors['nav']['container']            = 'has-background-primary has-border-primary';
        $colors['search_popup_container']      = 'has-text-primary-invert';
        $colors['lang_nav']['link__default']   = 'has-text-primary';
        $colors['lang_nav']['link__active']    = 'has-background-primary has-text-primary-invert';
        $colors['lang_nav']['dropdown_toggle'] = 'is-outlined is-small';
        $colors['fly_out_nav']['inner']        = 'has-text-primary-invert';
        $colors['fly_out_nav']['search_title'] = 'has-text-white';
        $colors['search_button']               = 'is-primary-invert';

        return $colors;
    }

    /**
     * Footer
     *
     * @param array $classes Footer classes.
     *
     * @return array
     */
    public function footer( array $classes ) : array {
        $classes['container']   = '';
        $classes['back_to_top'] = 'is-outlined';
        $classes['link']        = 'has-text-paragraph';
        $classes['link_icon']   = 'is-secondary';

        return $classes;
    }

    /**
     * Search classes.
     *
     * @param array $classes Search view classes.
     *
     * @return array
     */
    public function search_classes( $classes ) : array {
        $classes['event_search_section'] = '';

        return $classes;
    }
}
