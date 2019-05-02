<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2019-01-03
 * Time: 1:54 PM
 */

class WPGH_Dashboard_Widgets
{

    /**
     * @var array an array of all the available widgets
     */
    public $widgets = array();

    /**
     * WPGH_Dashboard_Widgets constructor.
     */
    public function __construct()
    {
        add_action( 'admin_init', array( $this, 'setup_widgets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'setup_dashboard_widgets' ) );
        add_action( 'groundhogg/api/v3/init_reports', array( $this, 'setup_dashboard_widgets' ) );
    }

    /**
     * Allow for use of dashboard widgets on other pages
     */
    public function setup_dashboard_widgets()
    {
       do_action( 'groundhogg/reports/load' );
    }

    public function __set( $key, $val ){
        $this->widgets[ $key ] = $val;
    }

    public function __get( $key )
    {
        if ( key_exists( $key, $this->widgets) ){
            return $this->widgets[ $key ];
        }

        return false;
    }

    public function setup_widgets()
    {

        if ( ! current_user_can( 'view_reports' ) ){
            return;
        }

        $this->includes();

        $this->widgets[] = new WPGH_Time_Range_Widget();
        $this->widgets[] = new WPGH_Report_Send_Emails();
        $this->widgets[] = new WPGH_Report_Form_Activity();
        $this->widgets[] = new WPGH_Report_Optins();
        $this->widgets[] = new WPGH_Most_Active_Funnels_Widget();
        $this->widgets[] = new WPGH_Funnel_Breakdown_Widget();
        $this->widgets[] = new WPGH_Lead_Source_Widget();
        $this->widgets[] = new WPGH_Social_Media_Widget();
        $this->widgets[] = new WPGH_Search_Engines_Widget();
        $this->widgets[] = new WPGH_Source_Page_Widget();
        $this->widgets[] = new WPGH_UTM_Campaign_Widget();
        $this->widgets[] = new WPGH_Geographic_Country_Report();
        $this->widgets[] = new WPGH_Geographic_Region_Report();
        $this->widgets[] = new WPGH_Optin_Status_Report();
        $this->widgets[] = new WPGH_Last_Broadcast_Report();

        do_action( 'groundhogg/dashboard/widgets/init', $this );

    }

    public function scripts( $hook_suffix ){

        if ( $hook_suffix !== 'index.php' ){
            return;
        }

        if ( ! current_user_can( 'view_reports' ) ){ return; }

        wp_enqueue_style( 'groundhogg-admin-dashboard' );
        wp_enqueue_script( 'groundhogg-admin-dashboard' );

        wp_localize_script( 'groundhogg-admin-dashboard', 'wpghDashboard', array(
            'date_range' => $this->range,
            'custom_date_range_start' => esc_attr( $this->get_url_var( 'custom_date_range_start' ) ),
            'custom_date_range_end' => esc_attr( $this->get_url_var( 'custom_date_range_end' ) )
        ) );

        wp_enqueue_script( 'jquery-flot' );
        wp_enqueue_script( 'jquery-flot-pie' );
        wp_enqueue_script( 'jquery-flot-categories' );
        wp_enqueue_script( 'jquery-flot-time' );
    }

    /**
     * Get a query var
     *
     * @param $var
     * @param $default
     * @return string
     */
    public function get_url_var( $var, $default = false )
    {
        if ( isset( $_REQUEST[ $var ] ) && ! empty( $_REQUEST[ $var ] ) ){
            return sanitize_text_field( urldecode( $_REQUEST[ $var ] ) );
        }

        return $default;
    }

    /**
     * @param $widget WPGH_Dashboard_Widget
     *
     * @return bool whether the widget was added or not.
     */
    public function add_widget( $widget )
    {
        if ( ! current_user_can( 'view_reports' ) ){
            return false;
        }

        $this->widgets[] = $widget;

        return true;
    }

    /**
     * include all nthe report files...
     */
    public function includes()
    {
        include_once dirname(__FILE__) . '/class-wpgh-dashboard-widget.php';
        include_once dirname(__FILE__) . '/class-wpgh-reporting-widget.php';
        include_once dirname(__FILE__) . '/class-wpgh-lead-source-report-widget.php';
        include_once dirname(__FILE__) . '/class-wpgh-line-graph-report-v2.php';
        include_once dirname(__FILE__) . '/class-wpgh-circle-graph-report.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-time-range-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-most-active-funnels.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-funnel-breakdown-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-report-optins.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-report-send-emails.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-report-form-activity.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-lead-source-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-social-media-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-search-engines-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-source-page-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-utm-campaign-widget.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-geographic-country-report.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-geographic-region-report.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-optin-status-report.php';
        include_once dirname(__FILE__) . '/widgets/class-wpgh-last-broadcast-report.php';
    }

}