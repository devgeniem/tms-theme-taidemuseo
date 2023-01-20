<?php
/**
 * Define the SingleExhibition class.
 */

use DustPress\Query;

/**
 * The SingleExhibition class.
 */
class SingleExhibition extends BaseModel {

    /**
     * Hooks
     */
    public function hooks() : void {
        add_filter( 'tms/theme/breadcrumbs/show_breadcrumbs_in_header', fn() => false );
    }

    /**
     * Content
     *
     * @return array|object|WP_Post|null
     * @throws Exception If global $post is not available or $id param is not defined.
     */
    public function content() {
        $single = Query::get_acf_post( get_queried_object_id() );
        $date   = self::get_date( $single->ID );

        if ( ! empty( $date ) ) {
            $single->date = $date;
        }

        return $single;
    }

    /**
     * Get & format opening times.
     *
     * @param int $id The post ID.
     */
    public static function get_date( $id ) {
        $start_date    = get_field( 'start_date', $id );
        $opening_times = '';

        if ( ! empty( $start_date ) ) {
            $opening_times = self::reformat_datetime_string( $start_date );
            $end_date      = get_field( 'end_date', $id );

            if ( ! empty( $end_date ) ) {
                $opening_times .= ' - ' . self::reformat_datetime_string( $end_date );
            }
        }

        return $opening_times;
    }

    /**
     * Format datetime string.
     *
     * @param string $string The date string.
     *
     * @return string
     */
    public static function reformat_datetime_string( $string ) {
        if ( empty( $string ) ) {
            return '';
        }

        $datetime = DateTime::createFromFormat( 'Y-m-d', $string );

        return $datetime->format( 'j.n.Y' );
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

        $end_date = DateTime::createFromFormat( $format, get_post_meta( $item->ID, 'end_date', true ) );

        return $today > $end_date;
    }
}
