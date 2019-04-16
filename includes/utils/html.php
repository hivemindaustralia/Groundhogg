<?php
namespace Groundhogg;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML
 *
 * Helper class for reusable html markup. Mostly input elements and form elements.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */
class HTML
{

	/**
     * WPGH_HTML constructor.
     *
     * Set up the ajax calls.
     */
    public function __construct()
    {
        add_action( 'wp_ajax_gh_get_contacts',   [ $this, 'ajax_get_contacts' ] );
        add_action( 'wp_ajax_gh_get_emails',     [ $this, 'ajax_get_emails' ] );
        add_action( 'wp_ajax_gh_get_sms',        [ $this, 'ajax_get_sms' ] );
        add_action( 'wp_ajax_gh_get_tags',       [ $this, 'ajax_get_tags' ] );
        add_action( 'wp_ajax_gh_get_benchmarks', [ $this, 'ajax_get_benchmarks' ] );
        add_action( 'wp_ajax_gh_get_meta_keys',  [ $this, 'ajax_get_meta_keys' ] );
    }

	/**
	 * Output a simple input field
	 *
	 * @param $args
	 * @return string
	 */
	public function input( $args )
	{
		$a = wp_parse_args( $args, array(
			'type'  => 'text',
			'name'  => '',
			'id'    => '',
			'class' => 'regular-text',
			'value' => '',
			'attributes' => '',
			'placeholder' => '',
			'required' => false
		) );

		if ( $a[ 'required' ] ){
			$a[ 'required' ] = 'required';
		}

		$html = sprintf(
			"<input type='%s' id='%s' class='%s' name='%s' value='%s' placeholder='%s' %s %s>",
			esc_attr( $a[ 'type'    ] ),
			esc_attr( $a[ 'id'      ] ),
			esc_attr( $a[ 'class'   ] ),
			esc_attr( $a[ 'name'    ] ),
			esc_attr( $a[ 'value'   ] ),
			esc_attr( $a[ 'placeholder' ] ),
			$a[ 'attributes'  ],
			$a[ 'required'  ]
		);

		return apply_filters( 'groundhogg/html/input', $html, $a );
	}

	/**
	 * Wrapper function for the INPUT
	 *
	 * @param $args
	 * @return string
	 */
	public function number( $args )
	{

		$a = wp_parse_args( $args, array(
			'type'  => 'number',
			'name'  => '',
			'id'    => '',
			'class' => 'regular-text',
			'value' => '',
			'attributes' => '',
			'placeholder' => '',
			'min'       => 0,
			'max'       => 99999,
			'step'      => 1
		) );

		if ( ! empty( $a[ 'max' ] ) ){
			$a[ 'attributes' ] .= sprintf( ' max="%d"', $a[ 'max' ] );
		}

		if ( ! empty( $a[ 'min' ] ) ){
			$a[ 'attributes' ] .= sprintf( ' min="%d"', $a[ 'min' ] );
		}

		if ( ! empty( $a[ 'step' ] ) ){
			$a[ 'attributes' ] .= sprintf( ' step="%s"', $a[ 'step' ] );
		}


		return apply_filters( 'groundhogg/html/number', $this->input( $a ), $a );
	}

	/**
	 * Output a button
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function button($args )
    {
        $a = wp_parse_args( $args, array(
            'type'      => 'button',
            'text'      => '',
            'name'      => '',
            'id'        => '',
            'class'     => 'button button-secondary',
            'value'     => '',
            'attributes' => '',
        ) );

        $html = sprintf(
            "<button type='%s' id='%s' class='%s' name='%s' value='%s' %s>%s</button>",
            esc_attr( $a[ 'type'    ] ),
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'class'   ] ),
            esc_attr( $a[ 'name'    ] ),
            esc_attr( $a[ 'value'   ] ),
             $a[ 'attributes'  ],
            esc_attr( $a[ 'text'  ] )
        );

        return apply_filters( 'groundhogg/html/button', $html, $a );
    }

	/**
	 * Output a checkbox
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function checkbox( $args )
	{
		$a = shortcode_atts( array(
			'label'         => '',
			'name'          => '',
			'id'            => '',
			'class'         => '',
			'value'         => '1',
			'checked'       => false,
			'title'         => '',
			'attributes'    => '',
			'required'      => false,
		), $args );

		$required = $a[ 'required' ] ? 'required' : '';
		$checked = $a[ 'checked' ] ? 'checked' : '';

		return apply_filters( 'groundhogg/html/checkbox', sprintf(
			"<label class='gh-checkbox-label'><input type='checkbox' name='%s' id='%s' class='%s' value='%s' title='%s' %s %s %s> %s</label>",
			esc_attr( $a[ 'name' ] ),
			esc_attr( $a[ 'id' ] ),
			esc_attr( $a[ 'class' ] ),
			esc_attr( $a[ 'value' ] ),
			esc_attr( $a[ 'title' ] ),
			$a[ 'attributes' ],
			$checked,
			$required,
			$a[ 'label' ]
		), $a );
	}

	/**
	 * Generate a link that activates the Groundhogg modal
	 *
	 * @param array $args
	 *
	 * @return mixed|void
	 */
    public function modal_link( $args = array() )
    {
        $a = wp_parse_args( $args, array(
            'title'     => 'Modal',
            'text'      => __( 'Open Modal', 'groundhogg' ),
            'footer_button_text' => __( 'Save Changes' ),
            'id'        => '',
            'class'     => 'button button-secondary',
            'source'    => '',
            'height'    => 500,
            'width'     => 500,
            'footer'    => 'true',
            'preventSave'    => 'true',
        ) );

        wpgh_enqueue_modal();

        $html = sprintf(
            "<a title='%s' id='%s' class='%s trigger-popup' href='#source=%s&footer=%s&width=%d&height=%d&footertext=%s&preventSave=%s' >%s</a>",
            esc_attr( $a[ 'title'  ] ),
            esc_attr( $a[ 'id'     ] ),
            esc_attr( $a[ 'class'  ] ),
            urlencode( $a[ 'source' ] ),
            esc_attr( $a[ 'footer' ] ),
            intval( $a[ 'width'    ] ),
            intval( $a[ 'height'   ] ),
            urlencode( $a[ 'footer_button_text' ] ),
            esc_attr( $a[ 'preventSave' ] ),
            $a[ 'text' ]
        );

        return apply_filters( 'groundhogg/html/modal_link', $html, $a );
    }

    /**
     * Wrapper function for the INPUT
     *
     * @param $args
     * @return string
     */
    public function range( $args )
    {

        $a = wp_parse_args( $args, array(
            'type'  => 'range',
            'name'  => '',
            'id'    => '',
            'class' => 'slider',
            'value' => '',
            'attributes' => '',
            'placeholder' => '',
            'min'       => 0,
            'max'       => 99999,
            'step'      => 1
        ) );

        if ( ! empty( $a[ 'max' ] ) ){
            $a[ 'attributes' ] .= sprintf( ' max="%d"', $a[ 'max' ] );
        }

        if ( ! empty( $a[ 'min' ] ) ){
            $a[ 'attributes' ] .= sprintf( ' min="%d"', $a[ 'min' ] );
        }

        if ( ! empty( $a[ 'step' ] ) ){
            $a[ 'attributes' ] .= sprintf( ' step="%s"', $a[ 'step' ] );
        }


	    return apply_filters( 'groundhogg/html/range', $this->input( $a ), $a );
    }

    /**
     * Output a simple textarea field
     *
     * @param $args
     * @return string
     */
    public function textarea( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'  => '',
            'id'    => '',
            'class' => '',
            'value' => '',
            'cols'  => '100',
            'rows'  => '7',
            'placeholder'   => '',
            'attributes'    => ''
        ) );

        $html = sprintf(
            "<textarea id='%s' class='%s' name='%s' cols='%s' rows='%s' placeholder='%s' %s>%s</textarea>",
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'class'   ] ),
            esc_attr( $a[ 'name'    ] ),
            esc_attr( $a[ 'cols'    ] ),
            esc_attr( $a[ 'rows'    ] ),
            esc_attr( $a[ 'placeholder' ] ),
            $a[ 'attributes'    ],
            $a[ 'value'         ]
        );

        return apply_filters( 'groundhogg/html/textarea', $html, $a );

    }

    /**
     * Output simple HTML for a dropdown field.
     *
     * @param $args
     * @return string
     */
    public function dropdown( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'              => '',
            'id'                => '',
            'class'             => '',
            'options'           => array(),
            'selected'          => '',
            'multiple'          => false,
            'option_none'       => 'Please Select One',
            'attributes'        => '',
            'option_none_value' => '',
        ) );

        $multiple           = $a[ 'multiple' ]             ? 'multiple'        : '';
        $a[ 'selected' ]    = is_array( $a[ 'selected' ] ) ? $a[ 'selected' ]  : array( $a[ 'selected' ] );

        $optionHTML = '';

        if ( ! empty( $a[ 'option_none' ] ) ){
            $optionHTML .= sprintf( "<option value='%s'>%s</option>",
                esc_attr( $a[ 'option_none_value' ] ),
                sanitize_text_field( $a[ 'option_none' ] )
            );
        }

        if ( ! empty( $a[ 'options' ] ) && is_array( $a[ 'options' ] ) )
        {
            $options = array_map( 'trim', $a[ 'options' ] );

            foreach ( $options as $value => $name ){

                $selected = ( in_array( $value, $a[ 'selected' ] ) ) ? 'selected' : '';

                $optionHTML .= sprintf(
                    "<option value='%s' %s>%s</option>",
                    esc_attr( $value ),
                    $selected,
                    sanitize_text_field( $name )
                );

            }

        }

        $html = sprintf(
            "<select name='%s' id='%s' class='%s' %s %s>%s</select>",
            esc_attr( $a[ 'name' ] ),
            esc_attr( $a[ 'id' ] ),
            esc_attr( $a[ 'class' ] ),
            $a[ 'attributes' ],
            $multiple,
            $optionHTML
        );

	    return apply_filters( 'groundhogg/html/textarea', $html, $a );

    }

    /**
     * Provide a dropdown for possible contact owners.
     * Includes all ADMINs, MARKETERS, and SALES MANAGERs
     *
     * @param $args
     * @return string
     */
    public function dropdown_owners( $args=array() )
    {

        $a = wp_parse_args( $args, array(
            'name'              => 'owner_id',
            'id'                => 'owner_id',
            'class'             => 'gh-owners',
            'options'           => array(),
            'selected'          => '',
            'multiple'          => false,
            'option_none'       => 'Please Select an Owner',
            'attributes'        => '',
            'option_none_value' => 0,
        ) );

        if ( empty( $a[ 'options' ] ) ){
            $owners = get_users( array( 'role__in' => array( 'administrator', 'marketer', 'sales_manager' ) ) );

            /**
             * @var $owner \WP_User
             */
            foreach ( $owners as $owner ){
                $a[ 'options' ][ $owner->ID ] = sprintf( '%s (%s)', $owner->display_name, $owner->user_email );
            }
        }

	    return apply_filters( 'groundhogg/html/dropdown_owners', $this->dropdown( $a ), $a );
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function round_robin( $args=array() )
    {
        $a = wp_parse_args( $args, array(
            'name'              => 'round_robin',
            'id'                => 'round_robin',
            'class'             => 'gh-select2',
            'data'              => array(),
            'selected'          => '',
            'multiple'          => true,
            'option_none'       => 'Please Select 1 or More Owners',
            'attributes'        => '',
            'option_none_value' => 0,
        ) );

        if ( empty( $a[ 'data' ] ) ){

            $owners = get_users( array( 'role__in' => array( 'administrator', 'marketer', 'sales_manager' ) ) );

            /**
             * @var $owner \WP_User
             */
            foreach ( $owners as $owner ){

                $a[ 'data' ][ $owner->ID ] = sprintf( '%s (%s)', $owner->display_name, $owner->user_email );

            }

        }

	    return apply_filters( 'groundhogg/html/round_robin', $this->select2( $a ), $a );
    }

    /**
     * Select 2 html input
     *
     * @param $args
     *
     * @type $selected array list of $value which are selected
     * @type $data array list of $value => $text options for the select 2
     *
     * @return string
     */
    public function select2( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'              => '',
            'id'                => '',
            'class'             => 'gh-select2',
            'data'              => array(),
            'selected'          => array(),
            'multiple'          => false,
            'placeholder'       => 'Please Select One',
            'attributes'        => '',
            'tags'              => false,
        ) );

        $multiple           = $a[ 'multiple' ]              ? 'multiple'             : '';
        $tags               = $a[ 'tags' ]                  ? 'data-tags="true"'     : '';

        $a[ 'selected' ]    = is_array( $a[ 'selected' ] )  ? $a[ 'selected' ]  : array( $a[ 'selected' ] );

        $optionHTML = '';

        if ( ! empty( $a[ 'data' ] ) && is_array( $a[ 'data' ] ) )
        {
            $options = $a[ 'data' ];

            $optionHTML .= sprintf(
                "<option value=''>%s</option>",
                $a[ 'placeholder' ]
            );

            foreach ( $options as $value => $name ){

                /* Include optgroup support */
                if ( is_array( $name ) ){

                    /* Redefine */
                    $inner_options = $name;
                    $label = $value;

                    $optionHTML .= sprintf( "<optgroup label='%s'>", $label );

                    foreach ( $inner_options as $inner_value => $inner_name ){

                        $selected = ( in_array( $inner_value, $a[ 'selected' ] ) ) ? 'selected' : '';

                        $optionHTML .= sprintf(
                            "<option value='%s' %s>%s</option>",
                            esc_attr( $inner_value ),
                            $selected,
                            sanitize_text_field( $inner_name )
                        );
                    }

                    $optionHTML .= "</optgroup>";

                } else {
                    $selected = ( in_array( $value, $a[ 'selected' ] ) ) ? 'selected' : '';

                    $optionHTML .= sprintf(
                        "<option value='%s' %s>%s</option>",
                        esc_attr( $value ),
                        $selected,
                        sanitize_text_field( $name )
                    );
                }

            }

        }

        $html = sprintf(
            "<select name='%s' id='%s' class='%s' data-placeholder='%s' %s %s %s>%s</select>",
            esc_attr( $a[ 'name' ] ),
            esc_attr( $a[ 'id' ] ),
            esc_attr( $a[ 'class' ] ),
            esc_attr( $a[ 'placeholder' ] ),
            $a[ 'attributes' ],
            $tags,
            $multiple,
            $optionHTML
        );

	    wp_enqueue_style( 'select2' );
	    wp_enqueue_script( 'select2' );
	    wp_enqueue_style( 'groundhogg-admin' );
	    wp_enqueue_script( 'groundhogg-admin' );

        return apply_filters( 'groundhogg/html/select2', $html, $a );

    }

	/**
     * Return the HTML for a tag picker
     *
     * @param $args
     * @return string
     */
    public function tag_picker( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'              => 'tags[]',
            'id'                => 'tags',
            'class'             => 'gh-tag-picker',
            'data'              => array(),
            'selected'          => array(),
            'multiple'          => true,
            'placeholder'       => __( 'Please Select a Tag', 'groundhogg' ),
            'tags'              => true,
        ) );

        if ( is_array( $a[ 'selected' ] ) ){

            foreach ( $a[ 'selected' ] as $tag_id ){

                if ( WPGH()->tags->exists( $tag_id ) ){

                    $tag = WPGH()->tags->get( $tag_id );

                    $a[ 'data' ][ $tag_id ] = sprintf( "%s (%s)", $tag->tag_name, $tag->contact_count );

                }

            }
        }


        return apply_filters( 'groundhogg/html/tag_picker', $this->select2( $a ), $a );
    }


	/**
     * Output a simple Jquery UI date picker
     *
     * @param $args
     * @return string HTML
     */
    public function date_picker( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'  => '',
            'id'    => uniqid( 'date-' ),
            'class' => 'regular-text',
            'value' => '',
            'attributes' => '',
            'placeholder' => '',
            'min-date' => date( 'Y-m-d', wpgh_convert_to_local_time( strtotime( 'today' ) ) ),
            'max-date' => date( 'Y-m-d', wpgh_convert_to_local_time( strtotime( '+100 years' ) ) ),
            'format' => 'yy-mm-dd'
        ) );

        $html = sprintf(
            "<input type='text' id='%s' class='%s' name='%s' value='%s' placeholder='%s' autocomplete='off' %s><script>jQuery(function($){\$('#%s').datepicker({changeMonth: true,changeYear: true,minDate: '%s', maxDate: '%s',dateFormat:'%s'})});</script>",
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'class'   ] ),
            esc_attr( $a[ 'name'    ] ),
            esc_attr( $a[ 'value'   ] ),
            esc_attr( $a[ 'placeholder' ] ),
            $a[ 'attributes'  ],
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'min-date' ] ),
            esc_attr( $a[ 'max-date' ] ),
            esc_attr( $a[ 'format' ] )
        );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-ui' );

        return apply_filters( 'groundhogg/html/date_picker', $html, $a );
    }

	/**
     * Return the HTML of a dropdown for contacts
     *
     * @param $args
     * @return string
     */
    public function dropdown_contacts( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'              => 'contact_id',
            'id'                => 'contact_id',
            'class'             => 'gh-contact-picker',
            'data'              => array(),
            'selected'          => array(),
            'multiple'          => false,
            'placeholder'       => __( 'Please select a contact', 'groundhogg' ),
            'tags'              => false,
        ) );

        foreach ( $a[ 'selected' ] as $contact_id ){

            $contact = wpgh_get_contact( $contact_id );

            if ( $contact->exists() ) {

                $a[ 'data' ][ $contact_id ] = sprintf( "%s %s (%s)", $contact->first_name, $contact->last_name, $contact->email );

            }

        }


        return apply_filters( 'groundhogg/html/dropdown_contacts', $this->select2( $a ), $a );
    }

	/**
     * Return the html for an email picker
     *
     * @param $args
     * @return string
     */
    public function dropdown_emails( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'              => 'email_id',
            'id'                => 'email_id',
            'class'             => 'gh-email-picker',
            'data'              => array(),
            'selected'          => array(),
            'multiple'          => false,
            'placeholder'       => __( 'Please select an email', 'groundhogg' ),
            'tags'              => false,
        ) );

        foreach ( $a[ 'selected' ] as $email_id ){

            if ( WPGH()->emails->exists( $email_id ) ){

                $email =  WPGH()->emails->get( $email_id );
                $a[ 'data' ][ $email_id ] = $email->subject . ' (' . $email->status . ')';

            }

        }

	    return apply_filters( 'groundhogg/html/dropdown_emails', $this->select2( $a ), $a );
    }


	/**
	 * Get json email results for email picker
	 */
	public function ajax_get_sms()
	{

		$query_args=[];

		if ( ! is_user_logged_in() || ! current_user_can( 'edit_sms' ) )
			wp_die( 'No access to sms.' );

		if ( isset(  $_REQUEST[ 'q' ] ) ){
			$query_args[ 'search' ] = $_REQUEST[ 'q' ];
		}

		$data = WPGH()->sms->get_smses( $query_args );

		$json = array();

		foreach ( $data as $i => $sms ) {

			$json[] = array(
				'id' => $sms->ID,
				'text' => $sms->title
			);

		}

		$results = array( 'results' => $json, 'more' => false );

		wp_die( json_encode( $results ) );
	}

	/**
	 * Return the html for an email picker
	 *
	 * @param $args
	 * @return string
	 */
	public function dropdown_sms( $args )
	{
		$a = wp_parse_args( $args, array(
			'name'              => 'sms_id',
			'id'                => 'sms_id',
			'class'             => 'gh-sms-picker',
			'data'              => array(),
			'selected'          => array(),
			'multiple'          => false,
			'placeholder'       => __( 'Please select an SMS', 'groundhogg' ),
			'tags'              => false,
		) );

		foreach ( $a[ 'selected' ] as $sms_id ){

			if ( WPGH()->sms->exists( $sms_id ) ){

				$email =  WPGH()->sms->get( $sms_id );
				$a[ 'data' ][ $sms_id ] = $email->title;

			}

		}

		return $this->select2( $a );
	}

	/**
	 * Get json email results for email picker
	 */
	public function ajax_get_benchmarks()
	{

		if ( ! is_user_logged_in() || ! current_user_can( 'edit_funnels' ) )
			wp_die( 'No access to benchmarks.' );

		if ( isset(  $_REQUEST[ 'q' ] ) ){
			$query_args[ 'search' ] = $_REQUEST[ 'q' ];
		}

		$query_args[ 'step_group' ] = 'benchmark';
		$data = WPGH()->steps->get_steps( $query_args );

		$json = array();

		foreach ( $data as $i => $step ) {

            $step = wpgh_get_funnel_step( $step->ID );

            if ( $step->is_active() ){

                $funnel_name = WPGH()->funnels->get_column_by( 'title', 'ID', $step->funnel_id );

                if ( isset( $json[ $funnel_name ] ) ){
                    $json[ $funnel_name ][ 'children' ][] = array(
                        'text' => sprintf( '%d. %s (%s)', $step->order, $step->title, str_replace( '_', ' ', $step->type ) ),
                        'id'   => $step->ID
                    );
                } else {
                    $json[ $funnel_name ] = array(
                        'text' => $funnel_name,
                        'children' => array(
                            array(
                                'text' => sprintf( '%d. %s (%s)', $step->order, $step->title, str_replace( '_', ' ', $step->type ) ),
                                'id'   => $step->ID
                            )
                        )
                    );
                }

            }

		}

		$results = array( 'results' => array_values( $json ), 'more' => false );

		wp_die( json_encode( $results ) );
	}

	/**
	 * Returns a picker for benchmarks.
	 * Included in core so that we don't need to include it in every extension we write.
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function benchmark_picker( $args=array() )
	{

		$a = wp_parse_args( $args, array(
			'name'              => 'benchmarks[]',
			'id'                => 'benchmarks',
			'class'             => 'gh-benchmark-picker',
			'data'              => array(),
			'selected'          => array(),
			'multiple'          => true,
			'placeholder'       => __( 'Please select 1 or more benchmarks', 'groundhogg' ),
			'tags'              => false,
		) );

//		$data = array();

		foreach ( $a[ 'selected' ] as $benchmark_id ){

		    $step = wpgh_get_funnel_step( $benchmark_id );

			if ( WPGH()->steps->exists( $benchmark_id ) && $step->is_active() ){
                $funnel_name = WPGH()->funnels->get_column_by( 'title', 'ID', $step->funnel_id );
                $a[ 'data' ][ $funnel_name ][ $step->ID ] = sprintf( "%d. %s (%s)", $step->order, $step->title, str_replace( '_', ' ', $step->type ) );
			}

		}

		return $this->select2( $a );
	}

	/**
     * Returns a select 2 compatible json object with contact data meta keys
     */
	public function ajax_get_meta_keys(){
        if ( ! is_user_logged_in() || ! current_user_can( 'view_contacts' ) )
            wp_die( 'No access to contacts.' );

        $json = array();

        $data = WPGH()->contact_meta->get_keys();

        foreach ( $data as $i => $key ) {

            $json[] = array(
                'id' => $key,
                'text' => $key
            );

        }

        $results = array( 'results' => $json, 'more' => false );

        wp_die( json_encode( $results ) );
    }

	/**
     * Get a meta key picker. useful for searching.
     *
     * @param array $args
     * @return string
     */
	public function meta_key_picker( $args=array() ){
        $a = wp_parse_args( $args, array(
            'name'              => 'key',
            'id'                => 'key',
            'class'             => 'gh-metakey-picker',
            'data'              => array(),
            'selected'          => array(),
            'multiple'          => false,
            'placeholder'       => __( 'Please select 1 or more meta keys', 'groundhogg' ),
            'tags'              => false,
        ) );

        foreach ( $a[ 'selected' ] as $key ){

            $a[ 'data' ][ $key ] = $key;

        }

        return $this->select2( $a );
    }

	/**
     * Return HTML for a color picker
     *
     * @param $args
     * @return string
     */
    public function color_picker( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'      => '',
            'id'        => '',
            'value'     => '',
            'default'   => ''
        ) );

        $html = sprintf(
            "<input type=\"text\" id=\"%s\" name=\"%s\" class=\"wpgh-color\" value=\"%s\" data-default-color=\"%s\" />",
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'name'    ] ),
            esc_attr( $a[ 'value'   ] ),
            esc_attr( $a[ 'default' ] )
        );



        return apply_filters( 'groundhogg/html/color_picker', $html, $args );
    }

	/**
     * This is for use withing the email editor.
     *
     * @param $args
     * @return string
     */
    public function font_picker( $args )
    {
        $a = wp_parse_args( $args, array(
            'name'      => '',
            'id'        => '',
            'selected'  => '',
            'fonts'     => array(
                'Arial, sans-serif'                                     => 'Arial',
                'Arial Black, Arial, sans-serif'                        => 'Arial Black',
                'Century Gothic, Times, serif'                          => 'Century Gothic',
                'Courier, monospace'                                    => 'Courier',
                'Courier New, monospace'                                => 'Courier New',
                'Geneva, Tahoma, Verdana, sans-serif'                   => 'Geneva',
                'Georgia, Times, Times New Roman, serif'                => 'Georgia',
                'Helvetica, Arial, sans-serif'                          => 'Helvetica',
                'Lucida, Geneva, Verdana, sans-serif'                   => 'Lucida',
                'Tahoma, Verdana, sans-serif'                           => 'Tahoma',
                'Times, Times New Roman, Baskerville, Georgia, serif'   => 'Times',
                'Times New Roman, Times, Georgia, serif'                => 'Times New Roman',
                'Verdana, Geneva, sans-serif'                           => 'Verdana',
            ),
        ) );

        /* set options so that parse args doesn't remove the fonts */
        $a[ 'options' ] = $a[ 'fonts' ];


        return apply_filters( 'groundhogg/html/font_picker', $this->dropdown( $a ), $a );

    }

	/**
     * Image picker, maimly for use by the email editor
     *
     * @param $args
     * @return string
     */
    public function image_picker( $args )
    {
        $a = wp_parse_args( $args, array(
            'id'        => '',
            'name'      => '',
            'class'     => '',
            'value'     => '',
        ) );

        $html = $this->input( array(
            'id'            => $a[ 'id' ],
            'name'          => $a[ 'id' ],
            'type'          => 'button',
            'value'         => __( 'Upload Image' ),
            'class'         => 'button gh-image-picker',
        ));

        $html.="<div style='margin-top: 10px;'></div>";

        $html .= $this->input( array(
            'id'    => $a[ 'id' ] . '-src',
            'name'  => $a[ 'id' ] . '-src',
            'placeholder' => __( 'Src' ),
            'class' => $a[ 'class' ]
        ) );

        $html .= $this->input( array(
            'id'    => $a[ 'id' ] . '-alt',
            'name'  => $a[ 'id' ] . '-alt',
            'placeholder' => __( 'Alt Tag' ),
            'class' => $a[ 'class' ]
        ) );

        $html .= $this->input( array(
            'id'    => $a[ 'id' ] . '-title',
            'name'  => $a[ 'id' ] . '-title',
            'placeholder' => __( 'Title' ),
            'class' => $a[ 'class' ]
        ) );

        wp_enqueue_media();
        wp_enqueue_style('groundhogg-admin' );
        wp_enqueue_script('groundhogg-admin-media-picker' );

        return apply_filters( 'groundhogg/html/image_picker', $html, $a );
    }

	/**
     * Autocomplete link picker
     *
     * @param $args
     * @return string
     */
    public function link_picker( $args )
    {
        $a = wp_parse_args( $args, array(
            'type'  => 'text',
            'name'  => '',
            'id'    => '',
            'class' => 'regular-text',
            'value' => '',
            'attributes' => '',
            'placeholder' => __( 'Start typing...', 'groundhogg' ),
            'autocomplete' => false,
            'required' => false
        ) );

        if ( $a[ 'required' ] ){
            $a[ 'required' ] = 'required';
        }

        $a[ 'autocomplete' ] = $a[ 'autocomplete' ] ? 'on' : 'off';

        $html = sprintf(
            "<input type='%s' id='%s' class='%s gh-link-picker' name='%s' value='%s' placeholder='%s' autocomplete='%s' %s %s>",
            esc_attr( $a[ 'type'    ] ),
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'class'   ] ),
            esc_attr( $a[ 'name'    ] ),
            esc_attr( $a[ 'value'   ] ),
            esc_attr( $a[ 'placeholder' ] ),
            esc_attr( $a[ 'autocomplete' ] ),
            $a[ 'attributes'  ],
            $a[ 'required'  ]
        );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style( 'groundhogg-admin' );
        wp_enqueue_script( 'groundhogg-admin' );

        return apply_filters( 'groundhogg/html/link_picker', $html, $args );
    }

	/**
	 * Output a progress bar.
	 *
	 * @param $args
	 *
	 * @return string
	 */
    public function progress_bar( $args=[] )
    {
	    $a = wp_parse_args( $args, array(
		    'id'        => '',
		    'class'     => '',
		    'hidden'    => false,
	    ) );

	    $hidden = ( $a[ 'hidden' ] ) ? 'hidden' : '';

	    $bar = sprintf( "<div id='%s-wrap' class=\"progress-bar-wrap %s %s\">
	            <div id='%s' class=\"progress-bar\">
	            <span id='%s-percentage' style='visibility: visible;float: none;padding-left: 30px;opacity: 1;' class=\"progress-percentage spinner\">0%%</span>
	            </div>
			</div>",
		    esc_attr( $a[ 'id' ] ),
		    esc_attr( $a[ 'class' ] ),
		    $hidden,
		    esc_attr( $a[ 'id' ] ),
		    esc_attr( $a[ 'id' ] )
	    );

        wp_enqueue_style( 'groundhogg-admin' );

        return apply_filters( 'groundhogg/html/progress_bar', $bar, $a );
    }

	/**
	 * Output a styled toggle switch.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function toggle( $args=[] )
    {
        $a = shortcode_atts( array(
            'name'          => '',
            'id'            => '',
            'class'         => '',
            'value'         => '1',
            'checked'       => false,
            'title'         => '',
            'attributes'    => '',
            'on'            => 'On',
            'off'           => 'Off',
        ), $args );

        $css = sprintf( "<style>#%s-switch .onoffswitch-inner:before {content: \"%s\";}
#%s-switch .onoffswitch-inner:after {content: \"%s\";}</style>",esc_attr( $a[ 'id' ] ), esc_attr( $a[ 'on' ] ), esc_attr( $a[ 'id' ] ), esc_attr( $a[ 'off' ] ) );

        wp_enqueue_style( 'groundhogg-admin' );

        $html = sprintf("%s<div id=\"%s-switch\" class=\"onoffswitch %s\" style=\"text-align: left\">
                        <input type=\"checkbox\" id=\"%s\" name=\"%s\" class=\"onoffswitch-checkbox %s\" value=\"%s\" %s>
                        <label class=\"onoffswitch-label\" for=\"%s\">
                            <span class=\"onoffswitch-inner\"></span>
                            <span class=\"onoffswitch-switch\"></span>
                        </label>
                    </div>",
            $css,
            esc_attr( $a[ 'id' ] ),
            esc_attr( $a[ 'class' ] ),
            esc_attr( $a[ 'id' ] ),
            esc_attr( $a[ 'name' ] ),
            esc_attr( $a[ 'class' ] ),
            esc_attr( $a[ 'value' ] ),
            $a[ 'checked' ] ? 'checked' : '',
            esc_attr( $a[ 'id' ] )
        );

	    return apply_filters( 'groundhogg/html/toggle', $html, $a );
    }

	/**
	 * Get json tag results for tag picker
	 */
	public function ajax_get_tags()
	{
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_tags' ) )
			wp_die( 'No access to tags.' );

		$value = isset( $_REQUEST[ 'q' ] ) ? sanitize_text_field( $_REQUEST[ 'q' ] ) : '';

		if ( empty( $value ) ){
			$tags = WPGH()->tags->get_tags();
		} else {
			$tags = WPGH()->tags->search( $value );
		}

		$json = array();

		foreach ( $tags as $i => $tag ) {

			$json[] = array(
				'id' => $tag->tag_id,
				'text' => sprintf( "%s (%s)", $tag->tag_name, $tag->contact_count )
			);

		}

		$results = array( 'results' => $json, 'more' => false );

		wp_send_json( $results );
	}

	/**
	 * Get json contact results for contact picker
	 */
	public function ajax_get_contacts()
	{
		if ( ! is_user_logged_in() || ! current_user_can( 'view_contacts' ) )
			wp_die( 'No access to contacts.' );

		$value = isset( $_REQUEST[ 'q' ] )? sanitize_text_field( $_REQUEST[ 'q' ] ) : '';

		$contacts = WPGH()->contacts->search( $value );

		$json = array();

		foreach ( $contacts as $i => $contact ) {

			$json[] = array(
				'id' => $contact->ID,
				'text' => sprintf( "%s %s (%s)", $contact->first_name, $contact->last_name, $contact->email )
			);

		}

		$results = array( 'results' => $json, 'more' => false );

		wp_send_json( $results );
	}

	/**
	 * Get json email results for email picker
	 */
	public function ajax_get_emails()
	{

		if ( ! is_user_logged_in() || ! current_user_can( 'edit_emails' ) )
			wp_die( 'No access to emails.' );

		if ( isset(  $_REQUEST[ 'q' ] ) ){
			$query_args[ 'search' ] = $_REQUEST[ 'q' ];
		}

		$query_args[ 'status' ] = 'ready';
		$data = WPGH()->emails->get_emails( $query_args );

		$query_args[ 'status' ] = 'draft';
		$data2 = WPGH()->emails->get_emails( $query_args );

		$data = array_merge( $data, $data2 );

		$json = array();

		foreach ( $data as $i => $email ) {

			$json[] = array(
				'id' => $email->ID,
				'text' => $email->subject . ' (' . $email->status . ')'
			);

		}

		$results = array( 'results' => $json, 'more' => false );

		wp_send_json( $results );
	}

}