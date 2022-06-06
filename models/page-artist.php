<?php
/**
 * Template Name: Taiteilija-arkisto
 */

use TMS\Theme\Base\Traits\Pagination;
use TMS\Theme\Taidemuseo\PostType\Artist;
use TMS\Theme\Taidemuseo\Taxonomy\ArtistCategory;

/**
 * Archive for Artist CPT
 */
class PageArtist extends BaseModel {

    use Pagination;

    /**
     * Template
     */
    const TEMPLATE = 'models/page-artist.php';

    /**
     * Search input name.
     */
    const SEARCH_QUERY_VAR = 'artist-search';

    /**
     * Artist category filter name.
     */
    const FILTER_QUERY_VAR = 'artist-filter';

    /**
     * Artist orderby var name.
     */
    const ORDERBY_QUERY_VAR = 'artist-sort';

    /**
     * Pagination data.
     *
     * @var object
     */
    protected object $pagination;

    /**
     * Hooks
     *
     * @return void
     */
    public static function hooks() {
        add_filter( 'redipress/ignore_query_vars', [ __CLASS__, 'set_ignored_query_vars' ], 10, 1 );
    }

    /**
     * Add custom query vars to the list of ignored query vars list for RediPress.
     *
     * @param array $vars Ignored query vars.
     *
     * @return array
     */
    public static function set_ignored_query_vars(
        array $vars
    ) : array {
        $vars[] = 'selection_year';

        return $vars;
    }

    /**
     * Get search query var value
     *
     * @return mixed
     */
    protected static function get_search_query_var() {
        return get_query_var( self::SEARCH_QUERY_VAR, false );
    }

    /**
     * Get filter query var value
     *
     * @return int|null
     */
    protected static function get_filter_query_var() {
        $value = get_query_var( self::FILTER_QUERY_VAR, false );

        return ! $value
            ? null
            : intval( $value );
    }

    /**
     * Get filter query var value
     *
     * @return string
     */
    protected static function get_orderby_query_var() {
        $value = get_query_var( self::ORDERBY_QUERY_VAR );

        return sanitize_text_field( $value );
    }

    /**
     * Modify query order.
     *
     * @param array $args WP Query args.
     */
    private static function modify_wp_query_order( array &$args ) : void {
        $orderby_query_var = self::get_orderby_query_var();

        // Default order: last name, ascending.
        if ( empty( $orderby_query_var ) ) {
            $args['orderby']  = [ 'last_name' => 'ASC' ];
            $args['meta_key'] = 'last_name';

            return;
        }

        // Last name, descending.
        if ( $orderby_query_var === 'desc' ) {
            $args['orderby']  = [ 'last_name' => 'DESC' ];
            $args['meta_key'] = 'last_name';

            return;
        }

        // Handle ordering by selection year
        $order = $orderby_query_var === 'selection_year_asc' ? 'ASC' : 'DESC';

        $args['meta_query'] = [
            'relation'          => 'AND',
            'selection_year_clause' => [
                'key' => 'selection_year',
            ],
            'last_name_clause'  => [
                'key' => 'last_name',
            ],
        ];
        $args['orderby']    = [ 'selection_year_clause' => $order, 'last_name_clause' => 'ASC' ];
    }

    /**
     * Page title
     *
     * @return string
     */
    public function page_title() : string {
        return get_the_title();
    }

    /**
     * Page description
     *
     * @return string
     */
    public function page_description() : string {
        return get_field( 'description' ) ?? '';
    }

    /**
     * Return translated strings.
     *
     * @return array[]
     */
    public function strings() : array {
        return [
            'search'         => [
                'label'             => __( 'Search for artist', 'tms-theme-taidemuseo' ),
                'submit_value'      => __( 'Search', 'tms-theme-taidemuseo' ),
                'input_placeholder' => __( 'Search query', 'tms-theme-taidemuseo' ),
            ],
            'terms'          => [
                'show_all' => __( 'Show All', 'tms-theme-taidemuseo' ),
            ],
            'no_results'     => __( 'No results', 'tms-theme-taidemuseo' ),
            'filter'         => __( 'Filter', 'tms-theme-taidemuseo' ),
            'sort'           => __( 'Sort', 'tms-theme-taidemuseo' ),
            'art_categories' => __( 'Categories', 'tms-theme-taidemuseo' ),

        ];
    }

    /**
     * Return current search data.
     *
     * @return string[]
     */
    public function search() : array {
        $this->search_data        = new stdClass();
        $this->search_data->query = get_query_var( self::SEARCH_QUERY_VAR );

        return [
            'input_search_name' => self::SEARCH_QUERY_VAR,
            'current_search'    => $this->search_data->query,
            'action'            => get_post_type_archive_link( Artist::SLUG ),
        ];
    }

    /**
     * Supply data for active filter hidden input.
     *
     * @return string[]
     */
    public function active_filter_data() : ?array {
        $active_filter = self::get_filter_query_var();

        return $active_filter ? [
            'name'  => self::FILTER_QUERY_VAR,
            'value' => $active_filter,
        ] : null;
    }

    /**
     * Filters
     *
     * @return array
     */
    public function filters() {
        $categories = get_field( 'artist_categories' );

        if ( empty( $categories ) || is_wp_error( $categories ) ) {
            return [];
        }

        $base_url   = get_the_permalink();
        $categories = array_map( function ( $item ) use ( $base_url ) {
            return [
                'name'      => $item->name,
                'url'       => add_query_arg(
                    [
                        self::FILTER_QUERY_VAR => $item->term_id,
                    ],
                    $base_url
                ),
                'is_active' => $item->term_id === self::get_filter_query_var(),
            ];
        }, $categories );

        array_unshift(
            $categories,
            [
                'name'      => __( 'All', 'tms-theme-taidemuseo' ),
                'url'       => $base_url,
                'is_active' => null === self::get_filter_query_var(),
            ]
        );

        return $categories;
    }

    /**
     * Sort options
     *
     * @return array
     */
    public function sort_options() {
        $current = self::get_orderby_query_var();

        $options = [
            [
                'label' => __( 'Name', 'tms-theme-taidemuseo' ),
                'value' => '',
            ],
            [
                'label' => __( 'Name, descending', 'tms-theme-taidemuseo' ),
                'value' => 'desc',
            ],
            [
                'label' => __( 'Young artist of the year ascending', 'tms-theme-taidemuseo' ),
                'value' => 'selection_year_asc',
            ],
            [
                'label' => __( 'Young artist of the year descending', 'tms-theme-taidemuseo' ),
                'value' => 'selection_year_desc',
            ],
        ];

        return array_map( function ( $item ) use ( $current ) {
            $item['is_selected'] = $item['value'] === $current ? 'selected' : '';

            return $item;
        }, $options );
    }

    /**
     * View results
     *
     * @return array
     */
    public function results() {
        $args = [
            'post_type' => Artist::SLUG,
            'paged'     => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
        ];

        self::modify_wp_query_order( $args );

        $categories = self::get_filter_query_var();

        if ( empty( $categories ) ) {
            $categories = get_field( 'artist_categories' );
            $categories = ! empty( $categories ) ? array_map( fn( $c ) => $c->term_id, $categories ) : [];
        }

        $args['tax_query'] = [
            [
                'taxonomy' => ArtistCategory::SLUG,
                'terms'    => $categories,
            ],
        ];

        $s = self::get_search_query_var();

        if ( ! empty( $s ) ) {
            $args['s'] = $s;
        }

        $artist_category = self::get_filter_query_var();

        if ( ! empty( $artist_category ) ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => ArtistCategory::SLUG,
                    'terms'    => $artist_category,
                ],
            ];
        }

        $s = self::get_search_query_var();

        if ( ! empty( $s ) ) {
            $args['s'] = $s;
        }

        $the_query = new WP_Query( $args );

        $this->set_pagination_data( $the_query );

        $search_clause = self::get_search_query_var();
        $is_filtered   = $search_clause || self::get_filter_query_var();

        return [
            'posts'   => $this->format_posts( $the_query->posts ),
            'summary' => $is_filtered ? $this->results_summary( $the_query->found_posts, $search_clause ) : false,
        ];
    }

    /**
     * Format posts for view
     *
     * @param array $posts Array of WP_Post instances.
     *
     * @return array
     */
    protected function format_posts( array $posts ) : array {
        return array_map( function ( $item ) {
            $item->permalink   = get_the_permalink( $item->ID );
            $additional_fields = get_fields( $item->ID );

            if ( ! empty( $additional_fields['selection_year'] ) ) {
                $item->years = $additional_fields['selection_year'];
            }

            $item->fields = $additional_fields;

            $item->link = [
                'url'          => $item->permalink,
                'title'        => __( 'View artist', 'tms-theme-taidemuseo' ),
                'icon'         => 'chevron-right',
                'icon_classes' => 'icon--medium',
            ];

            return $item;
        }, $posts );
    }

    /**
     * Set pagination data
     *
     * @param WP_Query $wp_query Instance of WP_Query.
     *
     * @return void
     */
    protected function set_pagination_data( $wp_query ) : void {
        $per_page = get_option( 'posts_per_page' );
        $paged    = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

        $this->pagination           = new stdClass();
        $this->pagination->page     = $paged;
        $this->pagination->per_page = $per_page;
        $this->pagination->items    = $wp_query->found_posts;
        $this->pagination->max_page = (int) ceil( $wp_query->found_posts / $per_page );
    }

    /**
     * Get results summary text.
     *
     * @param int    $result_count  Result count.
     * @param string $search_clause Search clause.
     *
     * @return string|bool
     */
    protected function results_summary( $result_count, $search_clause ) {
        if ( ! empty( $search_clause ) ) {
            $results_text = sprintf(
            // translators: 1. placeholder is number of search results, 2. placeholder contains the search term(s).
                _nx(
                    '%1$1s result found for "%2$2s"',
                    '%1$1s results found for "%2$2s"',
                    $result_count,
                    'filter with search clause results summary',
                    'tms-theme-taidemuseo'
                ),
                $result_count,
                $search_clause
            );
        }
        else {
            $results_text = sprintf(
            // translators: 1. placeholder is number of search results
                _nx(
                    '%1$1s result found',
                    '%1$1s results found',
                    $result_count,
                    'filter results summary',
                    'tms-theme-taidemuseo'
                ),
                $result_count,
                $search_clause
            );
        }

        return $results_text;
    }
}
