<?php
/**
 *  Copyright (c) 2021. Geniem Oy
 */

use TMS\Theme\Base\Traits\Pagination;
use TMS\Theme\Taidemuseo\PostType\Exhibition;

/**
 * Archive for Exhibition CPT
 */
class ArchiveExhibition extends BaseModel {

    use Pagination;

    /**
     * Search input name.
     */
    const SEARCH_QUERY_VAR = 'exhibition-search';

    /**
     * Artist category filter name.
     */
    const YEAR_QUERY_VAR = 'exhibition-year';

    /**
     * Artist category filter name.
     */
    const PAST_QUERY_VAR = 'archive';

    /**
     * Artist category filter name.
     */
    const UPCOMING_QUERY_VAR = 'upcoming';

    /**
     * Number of past items to show per page.
     */
    const PAST_ITEMS_PER_PAGE = '9';

    /**
     * Number of ongoing items to show per page.
     */
    const ONGOING_ITEMS_PER_PAGE = '100';

    /**
     * Number of upcoming items to show per page.
     */
    const UPCOMING_ITEMS_PER_PAGE = '100';

    /**
     * Pagination data.
     *
     * @var object
     */
    protected object $pagination;

    /**
     * Results data.
     *
     * @var object
     */
    protected object $results;

    /**
     * ArchiveExhibition constructor.
     *
     * @param array $args   Arguments.
     * @param null  $parent Parent.
     */
    public function __construct( $args = [], $parent = null ) {
        parent::__construct( $args, $parent );

        if ( is_post_type_archive( Exhibition::SLUG ) ) {
            $this->results = new stdClass();

            $args = [
                'post_type'      => Exhibition::SLUG,
                'posts_per_page' => self::ONGOING_ITEMS_PER_PAGE,
                'post_status'    => 'publish',
                'meta_query'     => [
                    'relation' => 'AND',
                    [
                        'start_date' => [
                            'key' => 'start_date',
                        ],
                        'is_main' => [
                            'key' => 'main_exhibition',
                        ],
                    ],
                ],
                'orderby'        => [ 'start_date' => 'ASC', 'is_main' => 'DESC', 'title' => 'ASC' ],
            ];

            $query = new WP_Query( $args );

            $this->results->all      = $query->have_posts() ? $query->posts : [];
            $this->results->upcoming = $query->have_posts()
                ? array_filter( $query->posts, [ $this, 'is_upcoming' ] )
                : [];
        }
    }

    /**
     * Hooks
     */
    public static function hooks() : void {
        add_action(
            'pre_get_posts',
            [ __CLASS__, 'modify_query' ]
        );
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
     * @return string|null
     */
    protected static function get_year_query_var() : ?string {
        $value = get_query_var( self::YEAR_QUERY_VAR, false );

        return ! $value
            ? null
            : sanitize_text_field( $value );
    }

    /**
     * Get current tab query var value
     *
     * @return bool
     */
    public function is_past_archive() : bool {
        return ! is_null( get_query_var( self::PAST_QUERY_VAR, null ) );
    }

    /**
     * Get current tab query var value
     *
     * @return bool
     */
    public function is_upcoming_archive() : bool {
        return ! is_null( get_query_var( self::UPCOMING_QUERY_VAR, null ) );
    }

    /**
     * Page title
     *
     * @return string
     */
    public function page_title() : string {
        return post_type_archive_title( '', false );
    }

    /**
     * Return translated strings.
     *
     * @return array[]
     */
    public function strings() : array {
        return [
            'search'         => [
                'label'             => __( 'Search from archive', 'tms-theme-taidemuseo' ),
                'submit_value'      => __( 'Search', 'tms-theme-taidemuseo' ),
                'input_placeholder' => __( 'Search from archive', 'tms-theme-taidemuseo' ),
            ],
            'no_results'     => __( 'No results', 'tms-theme-taidemuseo' ),
            'year_label'     => __( 'Year', 'tms-theme-taidemuseo' ),
            'upcoming_badge' => __( 'Upcoming', 'tms-theme-taidemuseo' ),
        ];
    }

    /**
     * Modify query
     *
     * @param WP_Query $wp_query Instance of WP_Query.
     *
     * @return void
     */
    public static function modify_query( WP_Query $wp_query ) : void {
        if ( is_admin() || ( ! $wp_query->is_main_query() || ! $wp_query->is_post_type_archive( Exhibition::SLUG ) ) ) {
            return;
        }

        $posts_per_page   = self::UPCOMING_ITEMS_PER_PAGE;
        $start_date_order = 'ASC';
        $instance         = new ArchiveExhibition();

        if ( $instance->is_past_archive() ) {
            if ( ! empty( $instance->results->upcoming ) ) {
                $wp_query->set( 'post__not_in', wp_list_pluck( $instance->results->upcoming, 'ID' ) );
            }

            $posts_per_page   = self::PAST_ITEMS_PER_PAGE;
            $start_date_order = 'DESC';

            $meta_query[] = [
                'key'     => 'end_date',
                'compare' => '<',
                'value'   => date( 'Ymd' ),
            ];

            $year = self::get_year_query_var();

            if ( ! empty( $year ) ) {
                $meta_query[] = [
                    'key'     => 'exhibition_year',
                    'compare' => '=',
                    'value'   => $year,
                ];
            }

            $wp_query->set( 'meta_query', $meta_query );

            $s = self::get_search_query_var();

            if ( ! empty( $s ) ) {
                $wp_query->set( 's', $s );
            }
        }

        $wp_query->set( 'orderby', [ 'start_date' => $start_date_order, 'main_exhibition', 'title' => 'ASC' ] );
        $wp_query->set( 'meta_key', 'start_date' );
        $wp_query->set( 'posts_per_page', $posts_per_page );
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
            'action'            => add_query_arg(
                self::PAST_QUERY_VAR,
                '',
                get_post_type_archive_link( Exhibition::SLUG )
            ),
        ];
    }

    /**
     * Return current search data.
     *
     * @return string[]
     */
    public function tabs() : array {
        $base_url            = get_post_type_archive_link( Exhibition::SLUG );
        $past_tab_active     = self::is_past_archive();
        $upcoming_tab_active = self::is_upcoming_archive();

        return [
            'upcoming' => [
                'text'      => __( 'Upcoming exhibitions', 'tms-theme-taidemuseo' ),
                'link'      => add_query_arg(
                    self::UPCOMING_QUERY_VAR,
                    '',
                    $base_url
                ),
                'is_active' => $upcoming_tab_active,
            ],
            'ongoing' => [
                'text'      => __( 'Current exhibitions', 'tms-theme-taidemuseo' ),
                'link'      => $base_url,
                'is_active' => ! $past_tab_active && ! $upcoming_tab_active,
            ],
            'past'     => [
                'text'      => __( 'Archives', 'tms-theme-taidemuseo' ),
                'link'      => add_query_arg(
                    self::PAST_QUERY_VAR,
                    '',
                    $base_url
                ),
                'is_active' => $past_tab_active,
            ],
        ];
    }

    /**
     * View results
     *
     * @return array
     */
    public function results() {
        global $wp_query;

        $is_past_archive     = $this->is_past_archive();
        $is_upcoming_archive = $this->is_upcoming_archive();
        $is_ongoing_archive  = ! $is_past_archive && ! $is_upcoming_archive;
        $per_page            = ( $is_past_archive ) ? self::PAST_ITEMS_PER_PAGE : ( ( $is_upcoming_archive ) ? self::UPCOMING_ITEMS_PER_PAGE : self::ONGOING_ITEMS_PER_PAGE );

        $current_exhibitions  = array_filter( $this->results->all, [ $this, 'is_current' ] );
        $upcoming_exhibitions = $this->results->upcoming;

        $unfiltered_past_exhibitions = array_filter( $this->results->all, [ $this, 'is_past' ] );
        $past_exhibitions            = $wp_query->posts;
        $this->results->past         = $past_exhibitions;

        $results = $is_past_archive ? $past_exhibitions : $upcoming_exhibitions;
        $this->set_pagination_data( count( $results ), $per_page );

        return [
            'result_count'           => count( $current_exhibitions ),
            'past_results_count'     => count( $unfiltered_past_exhibitions ),
            'upcoming_results_count' => count( $upcoming_exhibitions ),
            'show_past'              => $is_past_archive,
            'show_ongoing'           => $is_ongoing_archive,
            'show_upcoming'          => $is_upcoming_archive,
            'current_exhibitions'    => $this->format_posts( $current_exhibitions ),
            'upcoming_exhibitions'   => $this->format_posts( $upcoming_exhibitions ),
            'posts'                  => $this->format_posts( $results ),
            'summary'                => $this->results_summary( count( $results ) ),
            'have_posts'             => ! empty( $results ) || ! empty( $current_exhibitions ),
            'partial'                => $is_past_archive ? 'shared/exhibition-item-simple' : 'shared/exhibition-item',
        ];
    }

    /**
     * Years
     *
     * @return array
     */
    public function years() {
        if ( ! $this->is_past_archive() ) {
            return;
        }

        $choices = [];
        $items   = $this->results->all;

        if ( empty( $items ) ) {
            return $choices;
        }

        $selected_year = self::get_year_query_var();

        $choices[] = [
            'value'       => '',
            'is_selected' => empty( $selected_year ) ? 'selected' : '',
            'label'       => __( 'Filter by year', 'tms-theme-taidemuseo' ),
        ];

        foreach ( $items as $exhibition ) {
            $year = get_post_meta( $exhibition->ID, 'exhibition_year', true );

            if ( in_array( $year, array_column( $choices, 'value' ), true ) ) {
                continue;
            }

            if ( ! empty( $year ) ) {
                $choices[] = [
                    'value'       => $year,
                    'label'       => $year,
                    'is_selected' => $year === $selected_year ? 'selected' : '',
                ];
            }
        }

        return $choices;
    }

    /**
     * Is the items' end_date in the past?
     *
     * @param WP_Post $item Item object.
     *
     * @return bool
     */
    protected function is_past( $item ) {
        $format = 'Ymd';
        $today  = new DateTime( 'now' );
        $today->setTime( '0', '0' );

        $end_date = DateTime::createFromFormat( $format, get_post_meta( $item->ID, 'end_date', true ) );
        $end_date->setTime( '23', '59' );

        return $today > $end_date;
    }

    /**
     * Is the item currently running?
     *
     * @param WP_Post $item Item object.
     *
     * @return bool
     */
    protected function is_current( $item ) {
        $format = 'Ymd';
        $today  = new DateTime( 'now' );
        $today->setTime( '0', '0' );

        $start_date = DateTime::createFromFormat( $format, get_post_meta( $item->ID, 'start_date', true ) );
        $start_date->setTime( '0', '0' );

        $end_date = DateTime::createFromFormat( $format, get_post_meta( $item->ID, 'end_date', true ) );
        $end_date->setTime( '23', '59' );

        return $today >= $start_date && $today <= $end_date;
    }

    /**
     * Is the items' start date in the future?
     *
     * @param WP_Post $item Item object.
     *
     * @return bool
     */
    protected function is_upcoming( $item ) {
        $format = 'Ymd';
        $today  = new DateTime( 'now' );

        $start_date = DateTime::createFromFormat( $format, get_post_meta( $item->ID, 'start_date', true ) );

        return $start_date >= $today;
    }

    /**
     * Format posts for view
     *
     * @param array $posts Array of WP_Post instances.
     *
     * @return array|bool
     */
    protected function format_posts( array $posts ) {
        if ( empty( $posts ) ) {
            return false;
        }

        return array_map( function ( $item ) {
            $item->permalink   = get_the_permalink( $item->ID );
            $additional_fields = get_fields( $item->ID );
            $item->post_title  = $additional_fields['title'] ?: $item->post_title;
            $item->fields      = $additional_fields;
            $date              = SingleExhibition::get_date( $item->ID );

            if ( ! empty( $date ) ) {
                $item->date = $date;
            }

            if ( has_post_thumbnail( $item->ID ) ) {
                $item->image = get_post_thumbnail_id( $item->ID );
            }

            return $item;
        }, $posts );
    }

    /**
     * Set pagination data
     *
     * @param int    $count    Total result count.
     * @param string $per_page Number of items per page.
     */
    protected function set_pagination_data( $count, $per_page ) : void {
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

        $this->pagination           = new stdClass();
        $this->pagination->page     = $paged;
        $this->pagination->per_page = $per_page;
        $this->pagination->items    = $count;
        $this->pagination->max_page = (int) ceil( $count / $per_page );
    }

    /**
     * Get results summary text.
     *
     * @param int $result_count Result count.
     *
     * @return string|bool
     */
    protected function results_summary( $result_count ) {

        $search_clause = self::get_search_query_var();
        $is_filtered   = $search_clause || self::get_year_query_var();

        if ( ! $is_filtered ) {
            return false;
        }

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
