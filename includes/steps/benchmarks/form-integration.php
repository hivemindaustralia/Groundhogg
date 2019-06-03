<?php
namespace Groundhogg\Steps\Benchmarks;

use function Groundhogg\after_form_submit_handler;
use Groundhogg\Contact;
use function Groundhogg\generate_contact_with_map;
use function Groundhogg\get_array_var;
use function Groundhogg\get_mappable_fields;
use function Groundhogg\html;
use Groundhogg\Step;

/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-09-04
 * Time: 10:19 AM
 */

abstract class Form_Integration extends Benchmark
{

	/**
	 * Output the settings for the step, dropdown of all available contact forms...
	 *
	 * @param $step Step
	 */
	public function settings( $step )
	{

		html()->start_form_table();
		html()->start_row();
		html()->th( __( 'Run when this form is submitted', 'groundhogg' ) );
		html()->td( [
            html()->select2( [
                'id' => $this->setting_id_prefix( 'form_id' ),
                'name' => $this->setting_name_prefix( 'form_id' ),
                'data' => $this->get_forms_for_select_2(),
                'selected' => $this->get_setting( 'form_id' )
            ] ),
            html()->wrap(
                html()->modal_link( [
                    'title'     => __( 'Map Fields', 'groundhogg' ),
                    'text'      => __( 'Map Fields', 'groundhogg' ),
                    'footer_button_text' => __( 'Save Changes' ),
                    'id'        => '',
                    'class'     => 'button button-primary no-padding',
                    'source'    => $this->setting_id_prefix( 'field_map' ),
                    'height'    => 600,
                    'width'     => 600,
                    'footer'    => 'true',
                    'preventSave' => 'false',
                ] ),
                'div',
                [ 'class' => 'row-actions' ]
            ),
            html()->wrap( $this->field_map_table( $this->get_setting( 'form_id' ) ), 'div', [
                'class' => 'hidden',
                'id' => $this->setting_id_prefix( 'field_map' )
            ] )
        ] );
		html()->end_row();
		html()->end_form_table();
	}

    /**
     * Get the forms for a select2 picker.
     *
     * @return array
     */
	abstract protected function get_forms_for_select_2();

    /**
     * Returns an array of Ids => Labels for easy mapping.
     *
     * @param $form_id
     * @return array
     */
    abstract protected  function get_form_fields( $form_id );

    /**
     * Parse the filed into a normalize array.
     *
     * @param $key int|string
     * @param $field array|string
     * @return array
     */
    abstract protected function normalize_field( $key, $field );

    /**
     * @param $form_id
     * @return string
     */
	protected function field_map_table( $form_id )
    {

        $field_map = $this->get_setting( 'field_map' );
        $fields = $this->get_form_fields( $form_id );

        if  ( ! $fields ){
            return __( 'Please select a valid form and update first.', 'groundhogg' );
        }

        $rows = [];

        foreach ( $fields as $key => $field ){

            $row = $this->normalize_field( $key, $field );

            $rows[] = [
                $row[ 'id' ],
                $row[ 'label' ],
                html()->dropdown( [
                    'option_none'  => '* Do Not Map *',
                    'options'      => get_mappable_fields(),
                    'selected'     => get_array_var( $field_map, $row[ 'id' ] ),
                    'name'         => $this->setting_name_prefix( 'field_map' ) . sprintf( '[%s]', $row[ 'id' ] ),
                ] )
            ];

        }

        ob_start();

        html()->list_table(
            [
                'class'=>'field-map'
            ],
            [
                __( 'Field ID', 'groundhogg' ),
                __( 'Field Label', 'groundhogg' ),
                __( 'Map To', 'groundhogg' ),
            ],
            $rows
        );

        return ob_get_clean();
    }

    /**
     * Save the given step
     *
     * @param $step Step
     */
    public function save( $step )
    {
        $this->save_setting( 'form_id', absint( $this->get_posted_data( 'form_id' ) ) );
        $this->save_setting( 'field_map', array_map( 'sanitize_key', $this->get_posted_data( 'field_map', [] ) ) );
    }

    /**
     * Generate a contact from the map.
     *
     * @return false|Contact
     */
    public function get_the_contact()
    {
        // SKIP if not the right form.
        if ( ! $this->can_complete_step() ){
            return false;
        }

        $posted_data    = $this->get_data( 'posted_data' );
        $field_map      = $this->get_setting( 'field_map' );

        $contact = generate_contact_with_map( $posted_data, $field_map );

        if ( ! $contact || is_wp_error( $contact ) ){
            return false;
        }

        after_form_submit_handler( $contact );

        return $contact;
    }

    /**
     * Compare the Form ID is the only requirement.
     *
     * @return bool
     */
    public function can_complete_step()
    {
        return absint( $this->get_data( 'form_id' ) ) === absint( $this->get_setting( 'form_id' ) );
    }
}