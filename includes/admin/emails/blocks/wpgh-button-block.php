<?php
/**
 * Button block
 *
 * The button block used in the email builder
 *
 * @package     Admin
 * @subpackage  Admin/Emails/Blocks
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WPGH_Button_Block extends WPGH_Email_Block
{

    /**
     * Declare the block properties
     *
     * WPGH_Text_Block constructor.
     */
    public function __construct()
    {

        $this->icon = WPGH_ASSETS_FOLDER . 'images/email-icons/button.png' ;
        $this->name = 'button';
        $this->title = __( 'Button', 'groundhogg' );

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wpgh-button-block', WPGH_ASSETS_FOLDER . 'js/admin/email-blocks/button.min.js', array( 'wp-color-picker' ), filemtime( WPGH_PLUGIN_DIR . 'assets/js/admin/email-blocks/button.min.js' ) );

        parent::__construct();

    }

    /**
     * Return the inner html of the block
     *
     * @return string
     */
    protected function inner_html()
    {
        ob_start();

        ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr><td height="10"></td></tr>
            <tr>
                <td align="center">
                    <table border="0" cellspacing="0" cellpadding="0" style="margin-right: auto;margin-left: auto;">
                        <tr>
                            <td class="email-button" bgcolor="#EB7035" style="padding: 12px 18px 12px 18px; border-radius:3px" align="center"><b><a href="<?php echo site_url(); ?>" target="_blank" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; font-weight: bold; color: #ffffff; text-decoration: none !important; display: inline-block;"><?php _e('I am a button &rarr;'); ?></a></b></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td height="10"></td></tr>
        </table>
        <?php

        return ob_get_clean();
    }

    /**
     * Register the block settings panel
     *
     * @return array
     */
    protected function register_settings()
    {

        $this->settings = array(

            array(
                'type'  => 'input',
                'label' => __( 'Button Text' ),
                'atts'  => array(
                    'class' => 'input',
                    'id'    => 'button-text',
                    'name'  => 'button-text',
                ),
            ),
            array(
                'type'  => 'link_picker',
                'label' => __( 'Button Link' ),
                'atts'  => array(
                    'class' => 'input',
                    'id'    => 'button-link',
                    'name'  => 'button-link',
                ),
            ),
            array(
                'type'  => 'input',
                'label' => __( 'Button Color' ),
                'atts'  => array(
                    'name' => 'button-color',
                    'id' => 'button-color',
                    'value' => '#dd9933'
                ),
            ),
            array(
                'type'  => 'input',
                'label' => __( 'Font Color' ),
                'atts'  => array(
                    'name' => 'button-text-color',
                    'id' => 'button-text-color',
                    'value' => '#FFFFFF'
                ),
            ),
            array(
                'type'  => 'number',
                'label' => __( 'Font Size' ),
                'atts'  => array(
                    'class' => 'input',
                    'id'    => 'button-size',
                    'name'  => 'button-size',
                    'min'   => 10,
                    'max'   => 60,
                    'value' => 20,
                ),
            ),
            array(
                'type'  => 'font_picker',
                'label' => __( 'Button Font' ),
                'atts'  => array(
                    'name'      => 'button-font',
                    'id'        => 'button-font',
                ),
            ),
        );

        return parent::register_settings();

    }


}