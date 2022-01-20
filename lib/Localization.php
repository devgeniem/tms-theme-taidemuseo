<?php
/**
 *  Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo;

use TMS\Theme\Taidemuseo\Taxonomy\ArtistCategory;
use TMS\Theme\Taidemuseo\Taxonomy\ArtworkLocation;
use TMS\Theme\Taidemuseo\Taxonomy\ArtworkType;

/**
 * Class Localization
 *
 * @package TMS\Theme\Taidemuseo
 */
class Localization extends \TMS\Theme\Base\Localization implements \TMS\Theme\Base\Interfaces\Controller {

    /**
     * Load theme translations.
     */
    public function load_theme_textdomains() {
        \load_theme_textdomain(
            'tms-theme-base',
            get_template_directory() . '/lang'
        );

        \load_child_theme_textdomain(
            'tms-theme-taidemuseo',
            get_stylesheet_directory() . '/lang'
        );
    }
}
