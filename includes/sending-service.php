<?php
namespace Groundhogg;

use WP_Error;

class Sending_Service
{

    /**
     * List of errors.
     *
     * @var WP_Error[]
     */
    public $errors = [];

    /**
     * Sending_Service constructor.
     */
    public function __construct()
    {
        $should_listen = get_transient( 'gh_listen_for_connect' );

        if ( $should_listen && is_admin() ){
        	add_action( 'init', [ $this, 'connect_email_api' ] );
        }

        if ( $this->is_waiting_for_verification() ){
        	add_action( 'init', [ $this, 'setup_cron' ] );
        	add_action( 'groundhogg/sending_service/verify_domain', [ $this, 'check_verification_status' ] );
        }

        if ( $this->has_dns_records() ){
            add_action( 'groundhogg/settings/email/after_settings', [ $this, 'show_dns_in_settings' ] );
        }

        if ( is_admin() && isset_not_empty( $_REQUEST, 'test_gh_ss_connection' ) ){
            add_action( 'init', [ $this, 'send_test_email' ] );
        }

    }

    /**
     * Whether the site is waiting for verification
     *
     * @return bool
     */
    public function is_waiting_for_verification()
    {
        return Plugin::$instance->settings->is_option_enabled( 'email_api_check_verify_status' );
    }

    /**
     * @return bool
     */
    public function has_api_token()
    {
        return Plugin::$instance->settings->is_option_enabled( 'email_token' );
    }

    /**
     * @return string|false
     */
    public function get_api_token()
    {
        return Plugin::$instance->settings->get_option( 'email_token' );
    }

    /**
     * Get AWS DNS Records to add to a domain
     *
     * @return array|false
     */
    public function get_dns_records()
    {
        return Plugin::$instance->settings->get_option( 'email_api_dns_records' );
    }

    /**
     * Has Aws Records
     *
     * @return bool
     */
    public function has_dns_records()
    {
        return (bool) $this->get_dns_records();
    }

    /**
     * Whether the sending service is active for email
     *
     * @return bool
     */
    public function is_active_for_email()
    {
        return Plugin::$instance->settings->is_option_enabled( 'send_with_gh_api' );
    }

    /**
     * Whether transactional email should be sent using Groundhogg
     *
     * @return bool
     */
    public function is_active_for_transactional_email()
    {
        return Plugin::$instance->settings->is_option_enabled( 'send_all_email_through_ghss' );
    }

    /**
     * Whether the Groundhogg sending service is the system to use for SMS
     *
     * @return bool
     */
    public function is_active_for_sms()
    {
        return (bool) apply_filters( 'groundhogg/sending_service/send_sms', true );
    }

    /**
     * Get the Groundhogg User ID
     *
     * @return int
     */
    public function get_gh_uid()
    {
        $user_id = Plugin::$instance->settings->get_option( 'email_api_user_id' );
        return apply_filters( 'groundhogg/service_manager/register_domain/user_id', $user_id );
    }

    /**
     * Get the Groundhogg Oauth Token
     *
     * @return string
     */
    public function get_oauth_token()
    {
        $token = Plugin::$instance->settings->get_option( 'email_api_oauth_token' );
        return apply_filters( 'groundhogg/service_manager/register_domain/oauth_token', $token );
    }

    /**
     * @return int
     */
    public function get_remaining_email_credits()
    {
        return absint( Plugin::$instance->settings->get_option( 'remaining_api_credits' ) );
    }

    /**
     * @return int
     */
    public function get_remaining_sms_credits()
    {
        return absint( Plugin::$instance->settings->get_option( 'remaining_api_sms_credits' ) );
    }

    /**
     * Listen for option change for cron
     *
     * @param $val
     * @return array|false
     *
     * @todo do this.
     */
    public function manage_cron( $val )
    {
        if( $val === 'on' ){
            if( ! wpgh_get_option('gh_enable_cron_ghss') ) {
                $post = [
                    'domain'    => site_url(),
                    'user_id'   => $this->get_gh_uid(),
                ];
                $response = $this->request( 'cron/cron_enable', $post, 'POST' );
                if ( is_wp_error( $response ) ){
                    WPGH()->notices->add( $response );
                }
            }
            return true;
        } else {
            if( wpgh_get_option('gh_enable_cron_ghss') ) {
                $post = [
                    'domain'    => site_url(),
                    'user_id'   => $this->get_gh_uid(),
                ];
                $response = $this->request( 'cron/cron_disable', $post, 'POST' );

                if ( is_wp_error( $response ) ){
                    WPGH()->notices->add( $response );
                }
            }
            return false;
        }
    }

    /**
     * Setup a job to check the domain verification status.
     */
    public function setup_cron()
    {
        if ( ! wp_next_scheduled( 'groundhogg/sending_service/verify_domain' )  ){
            wp_schedule_event( time(), 'hourly' , 'groundhogg/sending_service/verify_domain' );
        }
    }

    /**
	 * Add a test connection button for the GHSS
	 */
    public function test_connection_ui(){

        if ( $this->has_api_token() ){
            ?>
            <a href="<?php echo wp_nonce_url( add_query_arg( 'test_gh_ss_connection', '1', $_SERVER[ 'REQUEST_URI' ] ), 'send_test_email' ); ?>" class="button-secondary"><?php _ex( 'Send Test Email', 'action', 'groundhogg' ) ?></a>
            <?php
        }

    }

    /**
     * Send a test email via GH_SS
     */
    public function send_test_email()
    {
        if ( ! wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'send_test_email' ) || ! current_user_can( 'send_email' ) ){
            return;
        }

        add_action( 'wp_mail_failed', [ $this, 'test_email_failed' ] );

        $result = gh_ss_mail( wp_get_current_user()->user_email, '[TEST] from the Groundhogg Sending Service', 'This is a test message to ensure the Groundhogg Sending Service is working.' );

        remove_action( 'wp_mail_failed', [ $this, 'test_email_failed' ] );

        if ( $result ){
            Plugin::$instance->notices->add( 'mail_success', sprintf( __( 'Test message sent successfully to %s!', 'groundhogg' ), wp_get_current_user()->user_email ) );
        }
    }

    /**
     * If the test email fails.
     *
     * @param $error WP_Error
     */
    public function test_email_failed( $error ){
        Plugin::$instance->notices->add( $error );
    }

    /**
     * Sends a request to Groundhogg.io to add this domain
     * Request returns a text record and a list of DKIM records
     */
    public function connect_email_api()
    {
        if ( ! is_admin()
             || $this->is_active_for_email()
             || ! isset_not_empty( $_GET, 'action' )
             || 'connect_to_gh' !== $_GET['action']
             || ! isset_not_empty( $_GET, 'token' )
             || ! current_user_can( 'manage_options' )
        ){
            return;
        }

        $token  = sanitize_text_field( urldecode( $_GET[ 'token' ] ) );
        $gh_uid = absint( $_GET[ 'user_id' ] );

        /* Update relevant options for further requests */
        Plugin::$instance->settings->update_option( 'gh_email_api_user_id', $gh_uid );
        Plugin::$instance->settings->update_option( 'gh_email_api_oauth_token', $token );

        $result = $this->register_domain();

        if ( is_wp_error( $result ) ){
            Plugin::$instance->notices->add( $result );
            return;
        }

        Plugin::$instance->notices->add( 'domain_registered', 'Successfully registered your domain.' );
    }

    /**
     * Register this domain
     * @param $domain string url of the site to register
     * @return bool|WP_Error
     */
    public function register_domain( $domain = '' )
    {

        if ( ! $domain ){
            $domain = site_url();
        }

        /* Use filters to retrieve the UID and TOKEN if whitelabel solution */
        $gh_uid = $this->get_gh_uid();
        $token = $this->get_oauth_token();

        if ( ! $gh_uid || ! $token ){
            return new WP_Error( 'invalid_credentials', 'Missing token or User ID.' );
        }

        $headers = [
            'Oauth-Token' => $token
        ];

        $post = [
            'domain'    => $domain,
            'user_id'   => $gh_uid,
        ];

        $json = $this->request( 'domains/add', $post, 'POST', $headers );

        if ( is_wp_error( $json ) ){
            return $json;
        }

        if ( ! isset( $json->dns_records ) ){
            return new WP_Error( 'no_dns', 'Could not retrieve DNS records.' );
        }

        /* Don't listen for connect anymore */
        delete_transient( 'gh_listen_for_connect' );

        /* Let WP know we should check for verification stats */
        Plugin::$instance->settings->update_option( 'email_api_check_verify_status', 1 );

        /* @type $json->dns_records array */
        Plugin::$instance->settings->update_option( 'email_api_dns_records', $json->dns_records );

        /**
         * @var $json object the JSON response from Groundhogg.io
         * @var $gh_uid int the User ID used to login
         * @var $token string the token used to connect.
         */
        do_action( 'groundhogg/service_manager/domain_registered', $json, $gh_uid, $token );

        return true;
    }

    /**
     * Send a request to Groundhogg.io to verify this domains status
     * Request provides domain status, and if verified an email token to use for sending
     */
    public function check_verification_status()
    {
        /* Use filters to retrieve the UID and TOKEN if whitelabel solution */
        $gh_uid = $this->get_gh_uid();
        $token = $this->get_oauth_token();

        if ( ! $gh_uid || ! $token ){
            return;
        }

        $headers = [
            'Oauth-Token' => $token
        ];

        $post = [
            'domain'    => site_url(),
            'user_id'   => $gh_uid,
        ];

        $response = $this->request( 'domains/verify', $post, 'POST', $headers );

        if ( is_wp_error( $response ) ){
            return;
        }

        /* If we got the token, set it and auto enable */
        if ( isset( $json->token ) ){
            Plugin::$instance->settings->update_option( 'email_token', sanitize_text_field( $response->token ) );
            Plugin::$instance->settings->update_option( 'send_with_gh_api', [ 'on' ] );

        	/* Domain is verified, no longer need to check verification */
            Plugin::$instance->settings->delete_option( 'email_api_check_verify_status' );
	        wp_clear_scheduled_hook( 'groundhogg/sending_service/verify_domain' );

            do_action( 'groundhogg/sending_service/domain_verified', $response );
        }
    }

    /**
     * @param $wperror WP_Error
     */
    public function add_error( $wperror ){
        if ( $wperror instanceof WP_Error ){
            $this->errors[] = $wperror;
        }
    }

    /**
     * @return bool
     */
    public function has_errors()
    {
        return ! empty( $this->errors );
    }

    /**
     * @return WP_Error[]
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     * @return WP_Error
     */
    public function get_last_error()
    {
        return $this->errors[ count( $this->errors ) - 1 ];
    }

    /**
	 * Show the DNS table in the settings where the email is located
	 */
	public function show_dns_in_settings()
	{

        ?>
        <h2><?php _ex( 'DNS Records', 'settings_page', 'groundhogg' ); ?></h2>
        <div style="max-width: 800px">
            <?php $this->get_dns_table(); ?>
        </div>
        <?php

	}

    /**
	 * Show the DNS Records table
	 */
	public function get_dns_table()
	{
		?>
        <p><?php _ex( 'Your account has been enabled to send emails & text messages! To finish this configuration, please add the following DNS records to your DNS zone.', 'guided_setup', 'groundhogg' ); ?>&nbsp;
            <a target="_blank" href="https://www.google.com/search?q=how+to+add+dns+record"><?php _ex( 'Learn about adding DNS records.', 'guided_setup', 'groundhogg' ); ?></a></p>
        <p><?php _ex( 'After you have added the DNS records your domain will be automatically verified and emails/sms will be sent using the Groundhogg Sending Service.', 'guided_setup', 'groundhogg' ); ?></p>
        <style>
            .full-width{ width: 100%}
            .widefat tr td:nth-child(2), .widefat tr th:nth-child(2){width: 50px;}
        </style>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th><?php _ex( 'Name', 'column_label' , 'groundhogg' ); ?></th>
                <th><?php _ex( 'Type', 'column_label' , 'groundhogg' ); ?></th>
                <th><?php _ex( 'Value', 'column_label', 'groundhogg'  ); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			$records = $this->get_dns_records();
			foreach ( $records as $record ): ?>
                <tr>
                    <td>
                        <input
                                type="text"
                                onfocus="this.select()"
                                class="full-width"
                                value="<?php esc_attr_e( $record->name ); ?>"
                                readonly>
                    </td>
                    <td><?php esc_html_e( $record->type ); ?></td>
                    <td> <input
                                type="text"
                                onfocus="this.select()"
                                class="full-width"
                                value="<?php esc_attr_e( $record->value ); ?>"
                                readonly></td>
                </tr>
			<?php endforeach;?>
            </tbody>
            <tfoot>
            <tr>
                <th><?php _ex( 'Name', 'column_label' , 'groundhogg' ); ?></th>
                <th><?php _ex( 'Type', 'column_label' , 'groundhogg' ); ?></th>
                <th><?php _ex( 'Value', 'column_label', 'groundhogg'  ); ?></th>
            </tr>
            </tfoot>
        </table>
		<?php
	}

    /**
     * Send a request to the gh SS.
     *
     * @param string $endpoint the REST endpoint
     * @param array $body the body of the request
     * @param string $method The request method
     * @param array $headers optional headers to override a request
     * @return object|WP_Error
     */
    public function request( $endpoint, $body=[], $method='POST', $headers=[] )
    {

        $method = strtoupper( $method );
        $url = sprintf( 'https://aws.groundhogg.io/wp-json/api/v2/%s', $endpoint );

        /* Set Default Headers */
        if ( empty( $headers ) ){
            $headers = [
                'Sender-Token'  => $this->get_api_token(),
                'Sender-Domain' => site_url(),
                'Content-Type'  => sprintf( 'application/json; charset=%s', get_bloginfo( 'charset' ) )
            ];
        }

        $body = is_array( $body ) ? wp_json_encode( $body ) : $body;

        $args = [
            'method'        => $method,
            'headers'       => $headers,
            'body'          => $body,
            'data_format'   => 'body',
            'sslverify'     => true
        ];

        if ( $method === 'GET' ){
            $response = wp_remote_get( $url, $args );
        } else {
            $response = wp_remote_post( $url, $args );
        }

        if ( ! $response ){
            $error = new WP_Error( 'unknown_error', sprintf( 'Failed to initialize remote %s.', $method ), $response );
            $this->add_error( $error );
            return $error;
        }

        if ( is_wp_error( $response ) ){
            $this->add_error( $response );
            return $response;
        }

        $json = json_decode( wp_remote_retrieve_body( $response ) );

        if ( ! $json ){
            $error = new WP_Error( 'unknown_error', sprintf( 'Failed to initialize remote %s.', $method ), wp_remote_retrieve_body( $response )  );
            $this->add_error( $error );
            return $error;
        }

        if ( is_json_error( $json ) ){
            $error = get_json_error( $json );
            $this->add_error( $error );
            return $error;
        }

        // Update num of credits remaining.
        if ( isset( $response->credits_remaining ) ){
            $credits = intval( $response->credits_remaining );
            Plugin::$instance->settings->update_option( 'remaining_api_credits', $credits );
            do_action( "groundhogg/sending_service/credits_used", $credits );
        }

        // Update num of SMS credits remaining.
        if ( isset( $response->sms_credits_remaining ) ){
            $credits = intval( $response->sms_credits_remaining );
            Plugin::$instance->settings->update_option( 'remaining_api_sms_credits', $credits );
            do_action( "groundhogg/sending_service/sms_credits_used", $credits );
        }

        return $json;

    }



}