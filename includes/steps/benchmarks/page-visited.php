<?php

namespace Groundhogg\Steps\Benchmarks;

use Groundhogg\Contact;
use Groundhogg\Plugin;
use Groundhogg\Step;

/**
 * Page Visited
 *
 * This will run whenever a page is visited
 *
 * @package     Elements
 * @subpackage  Elements/Benchmarks
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Page_Visited extends Benchmark
{

    /**
     * Get the element name
     *
     * @return string
     */
    public function get_name()
    {
        return _x( 'Page Visited', 'step_name', 'groundhogg' );
    }

    /**
     * Get the element type
     *
     * @return string
     */
    public function get_type()
    {
        return 'page_visited';
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function get_description()
    {
        return _x('Runs whenever the specified page is visited.', 'step_description', 'groundhogg');
    }

    /**
     * Get the icon URL
     *
     * @return string
     */
    public function get_icon()
    {
        return GROUNDHOGG_ASSETS_URL . '/images/funnel-icons/page-visited.png';
    }

    /**
     * @param $step Step
     */
    public function settings( $step )
    {

        $html = Plugin::$instance->utils->html;

        $html->start_form_table();

        $html->start_row();

        $html->th(
            __( 'Run when contact visits this page.', 'groundhogg' )
        );

        $html->td( [
            $html->dropdown( [
                'name'  => $this->setting_name_prefix( 'match_type' ),
                'id'    => $this->setting_id_prefix( 'match_type' ),
                'class'   => 'input',
                'options' => array(
                    'partial'   => __( 'Partial Match', 'groundhogg' ),
                    'exact'     => __( 'Exact Match', 'groundhogg' ),
                ),
                'selected' => $this->get_setting( 'match_type' ),
                'multiple' => false,
            ] ),
            $html->link_picker( [
                'name'  => $this->setting_name_prefix( 'url_match' ),
                'id'    => $this->setting_id_prefix( 'url_match' ),
                'value' => $this->get_setting( 'url_match' ),
                'class' => 'input'
            ] ),
            $html->description( __(
                'This will only work if the contact is logged in, clicked a link in an email or filled out a form before browsing.', 'groundhogg'
            ) )
        ] );

        $html->end_row();

        $html->end_form_table();
    }

    /**
     * Save the step settings
     *
     * @param $step Step
     */
    public function save( $step )
    {
        $this->save_setting( 'match_type', sanitize_text_field( $this->get_posted_data( 'match_type', 'exact' ) ) );
        $this->save_setting( 'url_match', sanitize_text_field( $this->get_posted_data( 'url_match' ) ) );
    }

    /**
     * Perform the complete action
     *
     * @param $ref string
     * @param $contact WPGH_Contact
     */
    public function complete( $ref, $contact )
    {
        $steps = $this->get_like_steps();

        if ( empty( $steps ) )
            return;

        $s = false;

        foreach ( $steps as $step ) {

            if ( $step->can_complete( $contact ) ){

                $match_type = $step->get_meta( 'match_type' );
                $match_url  = $step->get_meta( 'url_match' );

                if ( $match_type === 'exact' ){
                    $is_page = $ref === $match_url;
                } else {
                    $is_page = strpos( $ref, $match_url ) !== false;
                }

                if ( $is_page ){

                   $s = $step->enqueue( $contact );

                }
            }
        }
    }

    /**
     * get the hook for which the benchmark will run
     *
     * @return int[]
     */
    protected function get_complete_hooks()
    {
        return [ 'groundhogg/api/v3/steps/page-view', 2 ];
    }



    /**
     * Get the contact from the data set.
     *
     * @return Contact
     */
    protected function get_the_contact()
    {
        return Plugin::$instance->tracking->get_current_contact();
    }

    /**
     * Based on the current step and contact,
     *
     * @return bool
     */
    protected function can_complete_step()
    {

    }
}