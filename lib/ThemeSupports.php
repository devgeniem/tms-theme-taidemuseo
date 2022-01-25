<?php
/**
 * Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo;

use Closure;
use PageArtist;
use PageArtwork;
use TMS\Theme\Base\Interfaces\Controller;

/**
 * Class ThemeSupports
 *
 * @package TMS\Theme\Sara_Hilden
 */
class ThemeSupports implements Controller {

    /**
     * Initialize the class' variables and add methods
     * to the correct action hooks.
     *
     * @return void
     */
    public function hooks() : void {
        add_filter(
            'query_vars',
            Closure::fromCallable( [ $this, 'query_vars' ] )
        );
    }

    /**
     * Append custom query vars
     *
     * @param array $vars Registered query vars.
     *
     * @return array
     */
    protected function query_vars( $vars ) {
        $vars[] = PageArtist::SEARCH_QUERY_VAR;
        $vars[] = PageArtist::FILTER_QUERY_VAR;
        $vars[] = PageArtist::ORDERBY_QUERY_VAR;

        $vars[] = PageArtwork::SEARCH_QUERY_VAR;
        $vars[] = PageArtwork::FILTER_QUERY_VAR;

        return $vars;
    }
}
