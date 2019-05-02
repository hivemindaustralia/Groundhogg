<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2019-01-03
 * Time: 3:24 PM
 */

abstract class WPGH_Reporting_Widget extends WPGH_Dashboard_Widget
{

    protected static $js_flag = false;
    /**
     * @var int
     */
    public $start_time;

    /**
     * @var int
     */
    public $end_time;

    /**
     * @var int
     */
    public $start_range;

    /**
     * @var int
     */
    public $end_range;

    /**
     * @var string
     */
    public $range;

    /**
     * @var int;
     */
    public $points;

    /**
     * @var int
     */
    public $difference;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * A list of contacts that reporting widgets use.
     *
     * @var
     */
    protected static $contacts = null;

    /**
     * WPGH_Reporting_Widget constructor.
     */
    public function __construct()
    {
        $this->setup_range();
        $this->setup_reporting_time();

        add_action( 'wp_ajax_wpgh_export_' . $this->wid, array( $this, 'export' ) );

        parent::__construct();
    }

    /**
     * Output reporting args for a form if a refresh is necessary
     */
    protected function form_reporting_inputs()
    {
        printf( '<input type="hidden" value="%s" name="%s">', esc_attr( $this->range ), 'date_range' );
        printf( '<input type="hidden" value="%s" name="%s" >', esc_attr( $this->get_url_var( 'custom_date_range_start' ) ), 'custom_date_range_start' );
        printf( '<input type="hidden" value="%s" name="%s">', esc_attr( $this->get_url_var( 'custom_date_range_end' ) ), 'custom_date_range_end' );
    }

    protected function setup_range()
    {
        $this->range = $this->get_url_var( 'date_range', 'this_week' );
    }

    /**
     * Determine the reporting start and end time of the graph from input from the user.
     */
    protected function setup_reporting_time()
    {

        switch ( $this->range ){
            case 'today';
                $this->start_time   = strtotime( 'today' );
                $this->end_time     = $this->start_time + DAY_IN_SECONDS;
                $this->points       = 24;
                $this->difference   = HOUR_IN_SECONDS;
                break;
            case 'yesterday';
                $this->start_time   = strtotime( 'yesterday' );
                $this->end_time     = $this->start_time + DAY_IN_SECONDS;
                $this->points       = 24;
                $this->difference   = HOUR_IN_SECONDS;
                break;
            default:
            case 'this_week';
                $this->start_time   = mktime(0, 0, 0, date("n"), date("j") - date("N") + 1);
                $this->end_time     = $this->start_time + WEEK_IN_SECONDS;
                $this->points       = 7;
                $this->difference   = DAY_IN_SECONDS;
                break;
            case 'last_week';
                $this->start_time   = mktime(0, 0, 0, date("n"), date("j") - date("N") + 1) - WEEK_IN_SECONDS;
                $this->end_time     = $this->start_time + WEEK_IN_SECONDS;
                $this->points       = 7;
                $this->difference   = DAY_IN_SECONDS;
                break;
            case 'last_30';
                $this->start_time   = wpgh_round_to_day( time() - MONTH_IN_SECONDS );
                $this->end_time     = wpgh_round_to_day( time() );
                $this->points       = ceil( MONTH_IN_SECONDS / DAY_IN_SECONDS );
                $this->difference   = DAY_IN_SECONDS;
                break;
            case 'this_month';
                $this->start_time   = strtotime( 'first day of ' . date( 'F Y' ) );
//                var_dump( date( 'Y-m-d H:i:s', $this->start_time ) );
                $this->end_time     = strtotime( 'first day of ' . date( 'F Y', time() + MONTH_IN_SECONDS ) );
//                var_dump( date( 'Y-m-d H:i:s', $this->end_time ) );
                $this->points       = ceil( MONTH_IN_SECONDS / DAY_IN_SECONDS );
                $this->difference   = DAY_IN_SECONDS;
                break;
            case 'last_month';
                $this->start_time   = strtotime( 'first day of ' . date( 'F Y' , time() - MONTH_IN_SECONDS ) );
                $this->end_time     = strtotime( 'last day of ' . date( 'F Y' ) );
                $this->points       = ceil( MONTH_IN_SECONDS / DAY_IN_SECONDS );
                $this->difference   = DAY_IN_SECONDS;
                break;
            case 'this_quarter';
                $quarter            = wpgh_get_dates_of_quarter();
                $this->start_time   = $quarter[ 'start' ];
                $this->end_time     = $quarter[ 'end' ];
                $this->points       = ceil( ( $quarter[ 'end' ] - $quarter[ 'start' ] ) / WEEK_IN_SECONDS );
                $this->difference   = WEEK_IN_SECONDS;
                break;
            case 'last_quarter';
                $quarter            = wpgh_get_dates_of_quarter( 'previous' );
                $this->start_time   = $quarter[ 'start' ];
                $this->end_time     = $quarter[ 'end' ];
                $this->points       = ceil( ( $quarter[ 'end' ] - $quarter[ 'start' ] ) / WEEK_IN_SECONDS );
                $this->difference   = WEEK_IN_SECONDS;
                break;
            case 'this_year';
                $this->start_time   = mktime(0, 0, 0, 1, 1, date( 'Y' ) );
                $this->end_time     = $this->start_time + YEAR_IN_SECONDS;
                $this->points       = 12;
                $this->difference   = MONTH_IN_SECONDS;
                break;
            case 'last_year';
                $this->start_time   = mktime(0, 0, 0, 1, 1, date( 'Y' , time() - YEAR_IN_SECONDS ));
                $this->end_time     = $this->start_time + YEAR_IN_SECONDS;
                $this->points       = 12;
                $this->difference   = MONTH_IN_SECONDS;
                break;
            case 'custom';
                $this->start_time   = wpgh_round_to_day( strtotime( $this->get_url_var( 'custom_date_range_start' ) ) );
                $this->end_time     = wpgh_round_to_day( strtotime( $this->get_url_var( 'custom_date_range_end' ) ) );
                $range = $this->end_time - $this->start_time;
                $this->points       = ceil( $range  / $this->get_time_diff( $range ) );
                $this->difference   = $this->get_time_diff( $range );
                break;
        }

        $this->start_range = $this->start_time;
        $this->end_range = $this->start_range + $this->difference;
    }

    /**
     * Get the difference in time between points given a time range...
     *
     * @param $range
     * @return int
     */
    private function get_time_diff( $range )
    {

        if ( $range <= DAY_IN_SECONDS ){
            return HOUR_IN_SECONDS;
        } else if ( $range <= WEEK_IN_SECONDS ) {
            return DAY_IN_SECONDS;
        } else if ( $range <= MONTH_IN_SECONDS ){
            return WEEK_IN_SECONDS;
        } else if ( $range <= YEAR_IN_SECONDS ){
            return MONTH_IN_SECONDS;
        }

        return DAY_IN_SECONDS;

    }

    /**
     * @return array
     */
    protected function get_export_data()
    {
        return array();
    }

    /**
     * Ajax function to get export data CSV format.
     */
    public function export()
    {
        if ( ! current_user_can( 'export_reports' ) ){
            $response = _x( 'You cannot export reports!', 'notice', 'groundhogg' );
            wp_die(  $response  );
        }

        $this->range = stripslashes( $_POST[ 'date_range' ] );
        $this->setup_reporting_time();

        $data = $this->get_export_data();
        $response = is_array( $data ) ? json_encode( $data ) : $data;
        wp_die( $response );
    }

    /**
     * Output an export button that will export the report
     */
    protected function export_button()
    {
        if ( ! current_user_can( 'export_reports' ) ){
            return;
        }
        ?>
        <div class="export-button">
            <hr>
            <button id="<?php printf( 'export-%s', $this->wid ); ?>" type="button" class="export button button-secondary"><?php _ex( 'Export Report', 'action', 'groundhogg' ) ?></button>
            <span class="spinner"></span>
        </div>
        <?php
    }

    /**
     * Get contacts from within the time range of the reporting widget.
     *
     * @return array|object|null
     */
    public function get_contacts_created_within_time_range()
    {

        if ( self::$contacts !== null ){
            return self::$contacts;
        }

        global $wpdb;

        $table = WPGH()->contacts->table_name;
        $start_date = date('Y-m-d H:i:s', $this->start_time);
        $end_date = date('Y-m-d H:i:s', $this->end_time);

        self::$contacts = $wpdb->get_results("SELECT ID FROM $table WHERE '$start_date' <= date_created AND date_created <= '$end_date'");

        return self::$contacts;
    }

    /**
     * @return array Get just the IDs of the contacts
     */
    public function get_contact_ids_created_within_time_range()
    {
        return wp_parse_id_list( wp_list_pluck( $this->get_contacts_created_within_time_range() , 'ID' ) );
    }

	public static $meta_query_results = [];

	/**
	 * @param $meta_key
	 *
	 * @return array
	 */
	public function meta_query( $meta_key='' )
	{
		global $wpdb;
		$cache_key = md5( $meta_key );

		if ( key_exists( $cache_key, self::$meta_query_results ) ){
			return self::$meta_query_results[ $cache_key ];
		}

		$contact_ids = $this->get_contact_ids_created_within_time_range();
		$ids = implode( ',', $contact_ids );

		$results = [];

		if ( empty( $ids ) ){
			return $results;
		}

		$table_name = WPGH()->contact_meta->table_name;
		$results = wp_list_pluck( $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s AND contact_id IN ( $ids )", 'source_page' ) ), 'meta_value' );

		self::$meta_query_results[ $cache_key ] = $results;

		return $results;
	}

	/**
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return mixed|null|string
	 */
	public function meta_query_count( $meta_key='', $meta_value='' ){

		global $wpdb;

		$cache_key = md5( implode( '|', [ $meta_key, $meta_value ] ) );

		if ( key_exists( $cache_key, self::$meta_query_results ) ){
			return self::$meta_query_results[ $cache_key ];
		}

		$table_name = WPGH()->contact_meta->table_name;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_id) FROM {$table_name} WHERE meta_key = %s AND meta_value = %s AND contact_id IN ( {$this->get_id_list_string()} )", $meta_key, $meta_value ) );

		self::$meta_query_results[ $cache_key ] = $count;

		return $count;

	}

	/**
     * The list of IDs used in query
     *
	 * @return string
	 */
	public function get_id_list_string()
    {
	    $contact_ids = $this->get_contact_ids_created_within_time_range();

	    $list = implode( ',', $contact_ids );

	    if ( empty( $list ) )
	        return '0';

	    return $list;
    }

	/**
	 * @return array
	 */
    protected function get_data(){ return []; }
}