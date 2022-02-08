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

        add_filter(
            'tms/acf/block/subpages/data',
            [ $this, 'alter_block_subpages_data' ],
            30
        );
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
     * Alter subpages classes.
     *
     * @param array $data Block data.
     *
     * @return mixed
     */
    public function alter_block_subpages_data( $data ) {
        if ( empty( $data['subpages'] ) ) {
            return $data;
        }

        $icon_colors_map = [
            'black'     => 'is-secondary-invert',
            'white'     => 'is-primary',
            'primary'   => 'is-black-invert',
            'secondary' => 'is-secondary-invert',
        ];

        $icon_color_key = $data['background_color'] ?? 'black';

        $data['icon_classes'] = $icon_colors_map[ $icon_color_key ];

        return $data;
    }
}
