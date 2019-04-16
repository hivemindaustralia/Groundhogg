<?php
namespace Groundhogg;


use Groundhogg\DB\DB;
use Groundhogg\DB\Events;
use Groundhogg\DB\Meta_DB;
use Groundhogg\DB\Step_Meta;
use Groundhogg\DB\Steps;

if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Step
 *
 * Step is used to provide information about any kind of funnel step, benchmark, or action.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */
class Step extends Base_Object_With_Meta implements Event_Process
{
    const BENCHMARK = 'benchmark';
    const ACTION = 'action';

    /**
     * This is only used when the step is enqueuing itself...
     *
     * @since 1.0.16
     *
     * @var Contact
     */
    public $enqueued_contact;

    /**
     * Return the DB instance that is associated with items of this type.
     *
     * @return Steps
     */
    protected function get_db()
    {
        return Plugin::$instance->dbs->get_db( 'steps' );
    }

    /**
     * Return a META DB instance associated with items of this type.
     *
     * @return Step_Meta
     */
    protected function get_meta_db()
    {
        return Plugin::$instance->dbs->get_db( 'stepmeta' );
    }

    /**
     * Get the events DB
     *
     * @return Events
     */
    protected function get_events_db()
    {
        return Plugin::$instance->dbs->get_db( 'events' );
    }

    /**
     * Do any post setup actions.
     *
     * @return void
     */
    protected function post_setup()
    {
        // TODO: Implement post_setup() method.
    }

    /**
     * A string to represent the object type
     *
     * @return string
     */
    protected function get_object_type()
    {
        return 'step';
    }

    public function get_id()
    {
        return absint( $this->ID );
    }

    public function get_title()
    {
        return $this->step_title;
    }

    public function get_order()
    {
        return absint( $this->step_order );
    }

    public function get_type()
    {
        return $this->step_type;
    }

    public function get_group()
    {
        return $this->step_group;
    }

    public function get_funnel_id()
    {
        return absint( $this->funnel_id );
    }

    /**
     * @return Funnel
     */
    public function get_funnel()
    {
        return Plugin::$instance->utils->get_funnel( $this->get_funnel_id() );
    }

    /**
     * Get an array of contacts which are "waiting'
     * @return Contact[] | false
     */
    public function get_waiting_contacts()
    {
        $contacts = [];
        $events = $this->get_waiting_events();

        if ( ! $events ){
            return false;
        }

        foreach ( $events as $event ){
            $contacts[] = $event->get_contact();
        }

        return $contacts;
    }


    /**
     * Get an array of waiting events
     * @return Event[]|false
     */
    public function get_waiting_events()
    {
        $events = $this->get_events_db()->get_events( [
            'status'    => Event::WAITING,
            'step_id'   => $this->get_id(),
            'funnel_id' => $this->get_funnel_id(),
        ] );

        $prepped = [];

        if ( ! $events ){
            return false;
        }

        foreach ( $events as $event ) {
            $prepped[] = Plugin::$instance->utils->get_event( $event->ID );
        }

        return $prepped;
    }

    /**
     * @return bool whether the step is a benchmark
     */
    public function is_benchmark()
    {
       return $this->get_group() === self::BENCHMARK;
    }

    /**
     * @return bool whether the step is an action
     */
    public function is_action()
    {
        return $this->get_group() === self::ACTION;
    }

    /**
     * Get the next step in the order
     *
     * @return Step|false
     */
    public function get_next_action()
    {

        /* this will give an array of objects ordered by appearance in the funnel builder */
        $items = $this->get_funnel()->get_steps();

        if (  empty( $items ) ){
            /* something went wrong or there are no more steps*/
            return false;
        }

        $i = $this->get_order();

        if ( $i >= count( $items ) ) {

            /* This is the last step. */
            return false;

        }

        if ( $items[ $i ]->step_group === self::ACTION ){

            /* regardless of whether the current step is an action
            or a benchmark we can run the next step if it's an action */
            return $items[ $i ];

        }

        if ( $this->is_benchmark() ) {

            //todo verify comparison
            while ( $i < count( $items ) ) {

                if ( $items[ $i ]->step_group === self::ACTION ) {

                    return $items[ $i ];

                }

                $i++;

            }

        }

        return false;

    }

    /**
     * Get the delay time for enqueueing the next action
     *
     * @return int
     */
    public function get_delay_time()
    {
        $time = apply_filters( "groundhogg/elements/{$this->get_type()}/enqueue", $this );

        if ( ! is_numeric( $time ) ) {
            $time = time();
        }

        return $time;
    }

    /**
     * Do the event when being processed from the event queue...
     *
     * @param $contact Contact
     * @param $event Event
     *
     * @return bool|\WP_Error whether it was successful or not
     */
    public function run( $contact, $event = null )
    {
        if ( ! $this->is_active() ) {
            return false;
        }

        $this->switch_to_blog();

        do_action( "groundhogg/elements/{$this->get_type()}/run/before", $this  );

        $result = apply_filters( "groundhogg/elements/{$this->get_type()}/run", $contact, $event, $this );

        do_action( "groundhogg/elements/{$this->get_type()}/run/after", $this  );

        $this->restore_current_blog();

        return $result;
    }

    /**
     * Create an event and add it to the queue
     *
     * @param $contact Contact
     *
     * @return bool
     */
    public function enqueue( $contact )
    {
        $this->enqueued_contact = $contact;

        $this->get_events_db()->mass_update(
            [
                'status' => Event::SKIPPED
            ],
            [
                'funnel_id'     => $this->get_funnel_id(),
                'contact_id'    => $contact->get_id(),
                'event_type'    => Event::FUNNEL,
                'status'        => Event::WAITING

            ]
        );

        $event = [
            'time'          => $this->get_delay_time(),
            'funnel_id'     => $this->get_funnel_id(),
            'step_id'       => $this->get_id(),
            'event_type'    => Event::FUNNEL,
            'contact_id'    => $contact->get_id()
        ];

        $success = (bool) $this->get_events_db()->add( $event );

        return $success;
    }

    /**
     * Switches to the blog which the step can run on.
     */
    public function switch_to_blog()
    {
        if ( is_global_multisite() ) {
            $blog_id = $this->get_meta( 'blog_id' );
            if ( $blog_id && intval( $blog_id ) !== get_current_blog_id() ) {
                switch_to_blog( $blog_id );
            }
        }
    }

    /**
     * Restore the process to the current blog.
     */
    public function restore_current_blog()
    {
        if ( is_global_multisite() && ms_is_switched() ) {
            restore_current_blog();
        }
    }

    /**
     * Return whether or not the current action can run.
     * This was implement so that WPMU could be effectively implemented with the GLOBAL DB option enabled.
     *
     * Alwasy return true if not a multisite or multisite global is not enabled
     * otherwise compare the current blog ID to the blg ID associated with the step.
     */
    public function can_run()
    {

        if ( is_global_multisite() ){

            $blog_id = $this->get_meta( 'blog_id' );

            /* all blogs */
            if ( ! $blog_id ){

                return true;

            /* Current blog */
            } else if ( intval( $blog_id ) === get_current_blog_id() ){

                return true;

            /* Wrong Blog */
            } else {

                return false;

            }

        }

        return true;

    }

    /**
     * Whether this step can actually be completed
     * @param $contact Contact
     * @return bool
     */
    public function can_complete( $contact=null )
    {
        if ( $this->is_action() )
            return false;

        return $this->is_active() && ( $this->is_starting() || $this->contact_in_funnel( $contact ) );
    }


    /**
     * Returns whether the contact is currently in the funnel
     *
     * @param $contact Contact
     *
     * @return bool
     */
    public function contact_in_funnel( $contact )
    {
        return $this->get_events_db()->count( [ 'funnel_id' => $this->get_funnel_id(), 'contact_id' => $contact->get_id() ] ) > 0;
    }

    /**
     * Return whether the step/funnel is active?
     *
     * @return bool
     */
    public function is_active()
    {
        return $this->get_funnel()->is_active();
    }

    /**
     * Whether the step starts a funnel
     *
     * @return bool
     */
    public function is_starting()
    {
        if ( $this->is_action() )
            return false;

        if ( $this->get_order() === 1 )
            return true;

        $step_order = $this->get_order() - 1;

        while ( $step_order > 0 ){

            $steps = $this->get_funnel()->get_steps();

            $step = array_shift( $steps );

            if ( $step->is_action() ){
                return false;
            }

            $step_order -= 1;
        }

        return true;
    }

    /**
     * Return the name given with the ID prefixed for easy access in the $_POST variable
     *
     * @param $name
     * @return string
     */
    public function prefix( $name )
    {
        return $this->get_id() . '_' . esc_attr( $name );
    }

    /**
     * Get the ICON for the step.
     *
     * @see WPGH_Funnel_Step
     *
     * @return string
     */
    public function icon()
    {
        $icon = apply_filters( 'wpgh_step_icon_' . $this->get_type(), GROUNDHOGG_ASSETS_URL . 'images/funnel-icons/no-icon.png' );
        return apply_filters( "groundhogg/elements/{$this->get_type()}/icon", $icon );
    }

    /**
     * Output the reporting section for the step...
     *
     * @see WPGH_Funnel_Step
     */
    public function reporting()
    {

        do_action( "groundhogg/elements/{$this->get_type()}/reporting", $this );
        do_action( 'wpgh_get_step_reporting_' . $this->get_type(), $this );

    }

    /**
     * Output the settigns section for the step...
     *
     * @see WPGH_Funnel_Step
     */
    public function settings()
    {

        do_action( "groundhogg/elements/{$this->get_type()}/settings", $this );
        do_action( 'wpgh_get_step_settings_' . $this->get_type(), $this );

    }

    /**
     * Output the HTML of a step.
     */
    public function html()
    {
        $closed = $this->get_meta( 'is_closed' ) ? 'closed' : '' ;

        ?>
        <div title="<?php echo $this->title ?>" id="<?php echo $this->ID; ?>" class="postbox step <?php echo $this->group; ?> <?php echo $this->type; ?> <?php echo $closed; ?>">
            <button type="button" class="handlediv collapse"><span class="toggle-indicator" aria-hidden="true"></span></button>
            <input type="hidden" class="collapse-input" name="<?php echo $this->prefix( 'closed' ); ?>" value="<?php echo $this->get_meta( 'is_closed' ); ?>">
            <!-- DELETE -->
            <button title="Delete" type="button" class="handlediv delete-step">
                <span class="dashicons dashicons-trash"></span>
            </button>
            <!-- DUPLICATE -->
            <button title="Duplicate" type="button" class="handlediv duplicate-step">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
            <!-- HNDLE -->
            <h2 class="hndle ui-sortable-handle">
                <img class="hndle-icon" width="50" src="<?php echo $this->icon(); ?>">
                <?php $args = array(
                    'name'  => $this->prefix( 'title' ),
                    'id'    => $this->prefix( 'title' ),
                    'value' => __( $this->title, 'groundhogg' ),
                    'title' => __( 'Step Title', 'groundhogg' ),
                );

                echo WPGH()->html->input( $args ); ?>
                <?php if( wpgh_is_global_multisite() ): ?>
                    <!-- MULTISITE BLOG OPTION -->
                    <div class="wpmu-options">
                        <label style="padding-left: 30px">
                            <?php _e( 'Run on which blog?' ); ?>
                            <?php

                            $sites = get_sites();

                            $options = array();
                            foreach ( $sites as $site ){
                                $options[ $site->blog_id ] = get_blog_details($site->blog_id)->blogname;
                            }

                            echo WPGH()->html->dropdown( array(
                                'name'   => $this->prefix( 'blog_id' ),
                                'id'     => $this->prefix( 'blog_id' ),
                                'options' => $options,
                                'selected' => $this->get_meta( 'blog_id' ),
                                'option_none' => __( 'Any blog', 'groundhogg' )
                            ) );

                            ?>
                        </label>
                    </div>
                <?php endif; ?>
            </h2>
            <!-- INSIDE -->
            <div class="inside">
                <input type="hidden" name="steps[]" value="<?php echo $this->ID; ?>">
                <!-- SETTINGS -->
                <div class="step-edit <?php echo WPGH()->menu->funnels_page->reporting_enabled ? 'hidden' : '' ; ?>">
                    <div class="custom-settings">
                        <?php do_action( 'wpgh_step_settings_before', $this ); ?>
                        <?php do_action( 'groundhogg/step/settings/before', $this ); ?>
                        <?php $this->settings(); ?>
                        <?php do_action( 'wpgh_step_settings_after', $this ); ?>
                        <?php do_action( 'groundhogg/step/settings/after', $this ); ?>
                    </div>
                </div>
                <!-- REPORTING  -->
                <div class="step-reporting <?php echo WPGH()->menu->funnels_page->reporting_enabled ? '' : 'hidden' ; ?>">
                    <?php do_action( 'wpgh_step_reporting_before' ); ?>
                    <?php do_action( 'groundhogg/step/reporting/before' ); ?>
                    <?php $this->reporting(); ?>
                    <?php do_action( 'wpgh_step_reporting_after' ); ?>
                    <?php do_action( 'groundhogg/step/reporting/after' ); ?>
                </div>
            </div>
        </div>
        <?php

    }


    public function get_step_title()
    {
        return $this->get_title();
    }

    public function get_funnel_title()
    {
        return $this->get_funnel()->get_title();
    }

    /**
     * Get the HTML of the step and return it.
     *
     * @return false|string
     */
    public function __toString()
    {

        ob_start();

        $this->html();

        $html = ob_get_clean();

        return $html;
    }
}