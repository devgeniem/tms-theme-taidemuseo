<?php

namespace TMS\Theme\Taidemuseo\ACF\Layouts;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;

/**
 * Class LayoutInfoBadge
 *
 * @package TMS\Theme\Taidemuseo\ACF
 */
class ColorOptions {

    /**
     * LayoutInfoBadge constructor.
     */
    public function __construct() {

        add_filter(
            'tms/acf/layout/_articles/fields',
            [ $this, 'alter_fields' ],
            10,
            2
        );

        add_filter(
            'tms/acf/layout/articles/data',
            [ $this, 'alter_format' ],
            10
        );

        add_filter(
            'tms/acf/layout/blog_articles/data',
            [ $this, 'alter_format' ],
            10
        );

    }

    /**
     * Add badge fields.
     *
     * @param string $key Layout key.
     */
    public function get_fields( string $key ) : ?Field\Group {
        $group   = null;
        $strings = [
            'group' => [
                'label'        => 'Värivalinnat',
                'instructions' => '',
            ],
            'text_color'  => [
                'label'        => 'Tekstin väri',
                'instructions' => 'Valitse tekstin väri',
            ],
            'background_color' => [
                'label'        => 'Taustaväri',
                'instructions' => 'Valitse tai syötä taustan väri',
            ],
        ];

        try {
            $group = ( new Field\Group( $strings['group']['label'] ) )
                ->set_key( "${key}_color_options" )
                ->set_name( 'color_options' );

                $bg_color_field = ( new Field\Color( $strings['background_color']['label'] ) )
                ->set_key( "${key}_color_options_bg_color" )
                ->set_name( 'bg_color' )
                ->set_wrapper_width( 60 )
                ->set_instructions( $strings['background_color']['instructions'] );

            $text_color_field = ( new Field\Select( $strings['text_color']['label'] ) )
                ->set_key( "${key}_color_options_text_color" )
                ->set_name( 'text_color' )
                ->set_choices( [
                    'black' => 'Musta',
                    'white' => 'Valkoinen',
                ] )
                ->set_default_value( 'black' )
                ->set_wrapper_width( 20 )
                ->set_instructions( $strings['text_color']['instructions'] );

                $group->add_fields( [ $bg_color_field, $text_color_field ] );

        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $group;
    }

    /**
     * Add badge fields.
     *
     * @param array  $fields Array of ACF fields.
     * @param string $key    Layout key.
     */
    public function alter_fields( array $fields, string $key ) : array {
        try {
           
            $fields[] = $this->get_fields( $key );
         
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

        return $fields;
    }

    /**
     * Format layout data
     *
     * @param array $layout ACF Layout data.
     *
     * @return array
     */
    public function alter_format( array $layout ) : array {

        try {
           if ( ! empty( $layout['color_options']['bg_color'] )) {
            $bg_color = $layout['color_options']['bg_color'];
            $layout['bg_style'] =  sprintf( 'style="background-color:%s;"', $bg_color );
           }
           
           if ( ! empty( $layout['color_options']['text_color'] )) {
            $txt_color = $layout['color_options']['text_color'];
            $layout['txt_color_class'] = sprintf( 'has-text-%s', $txt_color );
           }
           else {
            $layout['txt_color_class'] = 'has-text-black';
           }

        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }

       

        return $layout;
    }
}

( new ColorOptions() );
