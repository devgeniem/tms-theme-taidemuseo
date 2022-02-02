<?php
/**
 * Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo;

use WP_post;
use function add_filter;

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

        add_filter( 'tms/theme/header/colors', [ $this, 'header' ] );
        add_filter( 'tms/theme/footer/colors', [ $this, 'footer' ] );

        add_filter( 'tms/theme/single_blog/classes', [ $this, 'single_blog_classes' ] );
        add_filter( 'comment_form_submit_button', [ $this, 'comments_submit' ], 15, 0 );
        add_filter( 'comment_reply_link', [ $this, 'reply_link_classes' ], 15, 1 );
    }

    /**
     * Header
     *
     * @param array $colors Color classes.
     *
     * @return array Array of customized colors.
     */
    public function header( $colors ) : array {
        $colors['nav']['container']            = 'has-background-primary-invert has-border-primary has-border-top-1 has-border-bottom-1';
        $colors['search_popup_container']      = 'has-background-primary-invert has-text-primary';
        $colors['lang_nav']['link__default']   = 'has-text-primary';
        $colors['lang_nav']['link__active']    = 'has-background-primary has-text-primary-invert';
        $colors['lang_nav']['dropdown_toggle'] = 'is-primary';
        $colors['fly_out_nav']['inner']        = 'has-background-light has-text-primary';

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
        $classes['container']   = 'has-colors-light';
        $classes['back_to_top'] = 'is-primary';
        $classes['link']        = 'has-text-paragraph';
        $classes['link_icon']   = 'is-secondary';

        return $classes;
    }

    /**
     * Override event item classes.
     *
     * @param array $classes Classes.
     *
     * @return array
     */
    public function single_blog_classes( $classes ) : array {
        $classes['info_section']         = '';
        $classes['info_section_authors'] = '';
        $classes['info_section_button']  = 'is-primary';

        return $classes;
    }

    /**
     * Override comment form submit button.
     *
     * @return string
     */
    public function comments_submit() : string {
        return sprintf(
            '<button name="submit" type="submit" id="submit" class="button button--icon is-primary" >%s %s</button>', // phpcs:ignore
            __( 'Send Comment', 'tms-theme-base' ),
            '<svg class="icon icon--arrow-right icon--large">
                <use xlink:href="#icon-arrow-right"></use>
            </svg>'
        );
    }

    /**
     * Customize reply link.
     *
     * @param string $link The HTML markup for the comment reply link.
     *
     * @return string
     */
    public function reply_link_classes( string $link ) : string {
        return str_replace( 'comment-reply-link', 'comment-reply-link is-small', $link );
    }
}
