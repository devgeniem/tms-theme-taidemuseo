<?php

namespace TMS\Theme\Taidemuseo\ACF\Fields\Settings;

use Geniem\ACF\Exception;
use Geniem\ACF\Field;
use Geniem\ACF\Field\Tab;
use TMS\Theme\Base\Logger;
use TMS\Theme\Base\PostType;

/**
 * Class ArtworkSettingsTab
 *
 * @package TMS\Theme\Taidemuseo\ACF\Tab
 */
class ArtworkSettingsTab extends Tab {

    /**
     * Where should the tab switcher be located
     *
     * @var string
     */
    protected $placement = 'left';

    /**
     * Tab strings.
     *
     * @var array
     */
    protected $strings = [
        'tab'                          => 'Teokset',
        'artwork_additional_info'      => [
            'title'        => 'Esitäytetyt lisätiedot',
            'instructions' => '',
        ],
        'artwork_additional_info_text' => [
            'title'        => 'Lisätiedon teksti',
            'instructions' => '',
        ],
        'artwork_archive_page'         => [
            'title'        => 'Teosten arkistosivu',
            'instructions' => '',
        ],
    ];

    /**
     * The constructor for tab.
     *
     * @param string $label Label.
     * @param null   $key   Key.
     * @param null   $name  Name.
     */
    public function __construct( $label = '', $key = null, $name = null ) { // phpcs:ignore
        $label = $this->strings['tab'];

        parent::__construct( $label );

        $this->sub_fields( $key );
    }

    /**
     * Register sub fields.
     *
     * @param string $key Field tab key.
     */
    public function sub_fields( $key ) {
        $strings = $this->strings;

        try {
            $info_repeater_field = ( new Field\Repeater( $strings['artwork_additional_info']['title'] ) )
                ->set_key( "${key}_artwork_additional_info" )
                ->set_name( 'artwork_additional_info' )
                ->set_instructions( $strings['artwork_additional_info']['instructions'] );

            $text_field = ( new Field\Text( $strings['artwork_additional_info_text']['title'] ) )
                ->set_key( "${key}_artwork_additional_info_text" )
                ->set_name( 'artwork_additional_info_text' )
                ->set_instructions( $strings['artwork_additional_info_text']['instructions'] );

            $info_repeater_field->add_field( $text_field );

            $artwork_list_page_field = ( new Field\PostObject( $strings['artwork_archive_page']['title'] ) )
                ->set_key( "${key}artwork_archive_page" )
                ->set_name( 'artwork_archive_page' )
                ->set_post_types( [ PostType\Page::SLUG ] )
                ->set_return_format( 'id' )
                ->set_instructions( $strings['artwork_archive_page']['instructions'] );

            $this->add_fields( [
                $info_repeater_field,
                $artwork_list_page_field,
            ] );
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTrace() );
        }
    }
}
