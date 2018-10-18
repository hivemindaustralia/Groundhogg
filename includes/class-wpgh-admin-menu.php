<?php
/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: 2018-08-16
 * Time: 8:06 PM
 */

class WPGH_Admin_Menu
{
    /**
     * @var WPGH_Settings_Page
     */
    var $settings_page;

    /**
     * @var WPGH_Emails_Page
     */
    var $emails_page;

    /**
     * @var WPGH_Funnels_Page
     */
    var $funnels_page;

    /**
     * @var WPGH_Superlinks_Page
     */
    var $superlink_page;

    /**
     * @var WPGH_Tags_Page
     */
    var $tags_page;

    /**
     * @var WPGH_Contacts_Page
     */
    var $contacts_page;

    /**
     * @var WPGH_Broadcasts_Page
     */
    var $broadcasts_page;

    /**
     * @var WPGH_Events_Page
     */
    var $events_page;

    /**
     * @var WPGH_Welcome_Page
     */
    var $welcome_page;

    /**
     * Register the pages...
     *
     * WPGH_Admin_Menu constructor.
     */
    function __construct()
    {
        $this->includes();

        $this->welcome_page     = new WPGH_Welcome_Page();
        $this->contacts_page    = new WPGH_Contacts_Page();
        $this->tags_page        = new WPGH_Tags_Page();
        $this->superlink_page   = new WPGH_Superlinks_Page();
        $this->broadcasts_page  = new WPGH_Broadcasts_Page();
        $this->emails_page      = new WPGH_Emails_Page();
        $this->funnels_page     = new WPGH_Funnels_Page();
        $this->events_page      = new WPGH_Events_Page();
        $this->settings_page    = new WPGH_Settings_Page();

    }

    public function includes()
    {
        require_once dirname( __FILE__ ). '/admin/broadcasts/class-wpgh-broadcasts-page.php';
        require_once dirname( __FILE__ ). '/admin/contacts/class-wpgh-contacts-page.php';
        require_once dirname( __FILE__ ). '/admin/emails/class-wpgh-emails-page.php';
        require_once dirname( __FILE__ ). '/admin/events/class-wpgh-events-page.php';
        require_once dirname( __FILE__ ). '/admin/funnels/class-wpgh-funnels-page.php';
        require_once dirname( __FILE__ ). '/admin/settings/class-wpgh-settings-page.php';
        require_once dirname( __FILE__ ). '/admin/superlinks/class-wpgh-superlinks-page.php';
        require_once dirname( __FILE__ ). '/admin/tags/class-wpgh-tags-page.php';
        require_once dirname( __FILE__ ). '/admin/welcome/class-wpgh-welcome-page.php';
    }

}