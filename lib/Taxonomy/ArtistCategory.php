<?php
/**
 * Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo\Taxonomy;

use \TMS\Theme\Base\Interfaces\Taxonomy;
use TMS\Theme\Taidemuseo\PostType\Artist;
use TMS\Theme\Base\Traits\Categories;

/**
 * This class defines the taxonomy.
 *
 * @package TMS\Theme\Taidemuseo\Taxonomy
 */
class ArtistCategory implements Taxonomy {

    use Categories;

    /**
     * This defines the slug of this taxonomy.
     */
    const SLUG = 'artist-category';

    /**
     * Add hooks and filters from this controller
     *
     * @return void
     */
    public function hooks() : void {
        add_action( 'init', \Closure::fromCallable( [ $this, 'register' ] ), 15 );
    }

    /**
     * This registers the post type.
     *
     * @return void
     */
    private function register() {
        $labels = [
            'name'                       => 'Avainsanat',
            'singular_name'              => 'Avainsana',
            'menu_name'                  => 'Avainsanat',
            'all_items'                  => 'Kaikki avainsanat',
            'new_item_name'              => 'Lisää uusi avainsana',
            'add_new_item'               => 'Lisää uusi avainsana',
            'edit_item'                  => 'Muokkaa avainsanaa',
            'update_item'                => 'Päivitä avainsana',
            'view_item'                  => 'Näytä avainsana',
            'separate_items_with_commas' => 'Erottele avainsanat pilkulla',
            'add_or_remove_items'        => 'Lisää tai poista avainsana',
            'choose_from_most_used'      => 'Suositut avainsanat',
            'popular_items'              => 'Suositut avainsanat',
            'search_items'               => 'Etsi avainsana',
            'not_found'                  => 'Ei tuloksia',
            'no_terms'                   => 'Ei tuloksia',
            'items_list'                 => 'Avainsanat',
            'items_list_navigation'      => 'Avainsanat',
        ];

        $args = [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'show_in_rest'      => true,
            'capabilities'      => [
                'manage_terms' => 'manage_artist_categories',
                'edit_terms'   => 'edit_artist_categories',
                'delete_terms' => 'delete_artist_categories',
                'assign_terms' => 'assign_artist_categories',
            ],
        ];

        register_taxonomy( self::SLUG, [ Artist::SLUG ], $args );
    }
}
