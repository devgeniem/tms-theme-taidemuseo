<?php
/**
 * Copyright (c) 2021. Geniem Oy
 */

namespace TMS\Theme\Taidemuseo\ACF;

use Closure;
use Geniem\ACF\Exception;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;
use Geniem\ACF\Field;
use TMS\Theme\Base\Logger;
use TMS\Theme\Taidemuseo\PostType\Exhibition;

/**
 * Class ExhibitionGroup
 *
 * @package TMS\Theme\Base\ACF
 */
class ExhibitionGroup {

    /**
     * ExhibitionGroup constructor.
     */
    public function __construct() {
        add_action(
            'init',
            Closure::fromCallable( [ $this, 'register_fields' ] )
        );

        add_action(
            'acf/update_value/key=fg_exhibition_fields_start_date',
            Closure::fromCallable( [ $this, 'update_year_meta_field' ] ),
            10,
            2
        );
    }

    /**
     * Register fields
     */
    protected function register_fields() : void {
        try {
            $field_group = ( new Group( 'Näyttelyn lisätiedot' ) )
                ->set_key( 'fg_exhibition_fields' );

            $rule_group = ( new RuleGroup() )
                ->add_rule( 'post_type', '==', Exhibition::SLUG );

            $field_group
                ->add_rule_group( $rule_group )
                ->set_position( 'normal' );

            $field_group->add_fields(
                apply_filters(
                    'tms/acf/group/' . $field_group->get_key() . '/fields',
                    [
                        $this->get_details_tab( $field_group->get_key() ),
                    ]
                )
            );

            $field_group = apply_filters(
                'tms/acf/group/' . $field_group->get_key(),
                $field_group
            );

            $field_group->register();
        }
        catch ( Exception $e ) {
            ( new Logger() )->error( $e->getMessage(), $e->getTraceAsString() );
        }
    }

    /**
     * Allowed filetypes in logo field. Affects logo instructions.
     *
     * @var array
     */
    private array $allowed_filetypes = [ 'jpg', 'png', 'svg' ];

    /**
     * Get writer tab
     *
     * @param string $key Field group key.
     *
     * @return Field\Tab
     * @throws Exception In case of invalid option.
     */
    protected function get_details_tab( string $key ) : Field\Tab {

        $file_types_list = implode( ', ', $this->allowed_filetypes );

        $strings = [
            'tab'         => 'Lisätiedot',
            'main_exhibition'  => [
                'title'        => 'Päänäyttely',
                'instructions' => 'Valittu näyttely näytetään listauksessa saman alkupäivämäärän näyttelyiden kanssa ylimpänä',
            ],
            'start_date'  => [
                'title'        => 'Alkupvm',
                'instructions' => '',
            ],
            'end_date'    => [
                'title'        => 'Loppupvm',
                'instructions' => '',
            ],
            'title'       => [
                'title'        => 'Otsikko',
                'instructions' => '',
            ],
            'location'    => [
                'title'        => 'Sijainti',
                'instructions' => '',
            ],
            'is_upcoming' => [
                'title'        => 'Tulossa',
                'instructions' => '',
            ],
            'logo_wall_header' => [
                'title'        => 'Logoseinän otsikko',
                'instructions' => '',
            ],
            'logo_wall_repeater' => [
                'title'        => 'Logoseinä',
                'instructions' => '',
                'button'       => 'Lisää logo',
            ],
            'logo_wall_logo'   => [
                'title'        => 'Logo',
                'instructions' => "Sallitut tiedostomuodot: {$file_types_list}.",
            ],
            'logo_wall_link'   => [
                'title'        => 'Linkki',
                'instructions' => '',
            ],
        ];

        $tab = ( new Field\Tab( $strings['tab'] ) )
            ->set_placement( 'left' );

        $title_field = ( new Field\Text( $strings['title']['title'] ) )
            ->set_key( "${key}_title" )
            ->set_name( 'title' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['title']['instructions'] );

        $display_format = 'j.n.Y';
        $return_format  = 'Y-m-d';

        $main_exhibition_field = ( new Field\TrueFalse( $strings['main_exhibition']['title'] ) )
            ->set_key( "${key}_main_exhibition" )
            ->set_name( 'main_exhibition' )
            ->set_wrapper_width( 50 )
            ->use_ui()
            ->set_instructions( $strings['main_exhibition']['instructions'] );

        $start_date_field = ( new Field\DatePicker( $strings['start_date']['title'] ) )
            ->set_key( "${key}_start_date" )
            ->set_name( 'start_date' )
            ->set_wrapper_width( 43 )
            ->set_display_format( $display_format )
            ->set_return_format( $return_format )
            ->set_instructions( $strings['start_date']['instructions'] );

        $end_date_field = ( new Field\DatePicker( $strings['end_date']['title'] ) )
            ->set_key( "${key}_end_date" )
            ->set_name( 'end_date' )
            ->set_wrapper_width( 43 )
            ->set_display_format( $display_format )
            ->set_return_format( $return_format )
            ->set_instructions( $strings['end_date']['instructions'] );

        $is_upcoming_field = ( new Field\TrueFalse( $strings['is_upcoming']['title'] ) )
            ->set_key( "${key}_is_upcoming" )
            ->set_name( 'is_upcoming' )
            ->set_wrapper_width( 14 )
            ->use_ui()
            ->set_instructions( $strings['is_upcoming']['instructions'] );

        $location_field = ( new Field\Text( $strings['location']['title'] ) )
            ->set_key( "${key}_location" )
            ->set_name( 'location' )
            ->set_instructions( $strings['location']['instructions'] );
        
        $logo_wall_header = ( new Field\Text( $strings['logo_wall_header']['title'] ) )
            ->set_key( "${key}_logo_wall_header" )
            ->set_name( 'logo_wall_header' )
            ->set_instructions( $strings['logo_wall_header']['instructions'] );

        $logo_wall_repeater = ( new Field\Repeater( $strings['logo_wall_repeater']['title'] ) )
            ->set_key( "${key}_logo_wall_repeater" )
            ->set_name( 'logo_wall_repeater' )
            ->set_layout( 'block' )
            ->set_button_label( $strings['logo_wall_repeater']['button'] );

        $logo_wall_logo_field = ( new Field\Image( $strings['logo_wall_logo']['title'] ) )
            ->set_key( "${key}_logo_wall_logo" )
            ->set_name( 'logo_wall_logo' )
            ->set_wrapper_width( 50 )
            ->set_mime_types( $this->allowed_filetypes )
            ->set_required()
            ->set_instructions( $strings['logo_wall_logo']['instructions'] );

        $logo_wall_link_field = ( new Field\Link( $strings['logo_wall_link']['title'] ) )
            ->set_key( "${key}_logo_wall_link" )
            ->set_name( 'logo_wall_link' )
            ->set_wrapper_width( 50 )
            ->set_instructions( $strings['logo_wall_link']['instructions'] );

        $logo_wall_repeater->add_fields( [ $logo_wall_logo_field, $logo_wall_link_field ] );

        $tab->add_fields( [
            $title_field,
            $main_exhibition_field,
            $start_date_field,
            $end_date_field,
            $is_upcoming_field,
            $location_field,
            $logo_wall_header,
            $logo_wall_repeater,
        ] );

        return $tab;
    }

    /**
     * Set meta key year based on start date field
     *
     * @param string $value   Date value as as string.
     * @param int    $post_id Target post ID.
     *
     * @return mixed
     */
    protected function update_year_meta_field( $value, $post_id ) {
        if ( empty( $value ) ) {
            return $value;
        }

        update_post_meta( $post_id, 'exhibition_year', substr( $value, 0, 4 ) );

        return $value;
    }
}

( new ExhibitionGroup() );
