<?php
/**
 *  Copyright (c) 2021. Geniem Oy
 */

/**
 * Alter Grid Fields block, layout data
 */
class AlterGridFieldsData {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter(
            'tms/acf/block/grid/data',
            [ $this, 'alter_format' ],
            20
        );
        add_filter(
            'tms/acf/layout/grid/data',
            [ $this, 'alter_format' ],
            20
        );

    }

    /**
     * Format layout data. Replace BG colors.
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function alter_format( array $layout ) : array {
        foreach ( $layout['repeater'] as $key => $item ) {
            $layout['repeater'][ $key ]['classes'] = str_replace( [ 'has-colors-primary'], [ 'has-colors-secondary' ], $layout['repeater'][ $key ]['classes'] ); // phpcs:ignore
            $layout['repeater'][ $key ]['button']  = 'is-primary has-text-weight-semibold';
        }
        return $layout;
    }
}

( new AlterGridFieldsData() );
