<?php
/**
 * This library helps us to deal with links to documentation
 * @copyright  Copyright (c) 2019-2020 Karl Joseph Saycon
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/ninjakwarl
 * @since         0.3.0
 */

if (!defined('BASEPATH')) { exit('No direct script access allowed'); }
 
/**
 * This class helps to build links to documentation pages
 */
class Help {

    /**
     * Access to CI framework so as to use other libraries
     * @var type Code Igniter framework
     */
    private $CI;

    /**
     * Default constructor
     * @author Karl Joseph Saycon <benjamin.balet@gmail.com>
     */
    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('language');
        $this->CI->load->library('session');
        $this->CI->lang->load('global', $this->CI->session->userdata('language'));
    }

    /**
     * Test if a help page is available and returns a help link if so
     * @param string $page name of a page of the application
     * @return string link to Help page or empty string
     * @author Karl Joseph Saycon <benjamin.balet@gmail.com>
     */
    public function create_help_link($page) {
        if (lang($page) != "") {
            return '&nbsp;' .
                      '<a href="' . lang($page) . '"' .
                      ' title="' . lang('global_link_tooltip_documentation') . '"' .
                      ' target="_blank" rel="nofollow"><i class="icon-question-sign"></i></a>';
        } else {
            return '';
        }        
    }

    /**
     * NOT USED AT THE MOMENT. MIGHT BE USED LATER
     * @param string $page name of a page of the application
     * @return string Default Help link
     * @author Karl Joseph Saycon <benjamin.balet@gmail.com>
     */
    public function get_default_help_page($page) {
        if (lang('global_link_doc_page_calendar_organization') == "") {
            $defaut['global_link_doc_page_calendar_organization'] = 'http://iamkarlsaycon.com/page-calendar-organization.html';
            $defaut['global_link_doc_page_my_summary'] = 'http://iamkarlsaycon.com/page-my-summary.html';
            $defaut['global_link_doc_page_request_leave'] = 'http://iamkarlsaycon.com/how-to-request-a-leave.html';
            $defaut['global_link_doc_page_edit_leave_type'] = 'http://iamkarlsaycon.com/edit-leave-types.html';
            $defaut['global_link_doc_page_hr_organization'] = 'http://iamkarlsaycon.com/page-describe-organization.html';
            $defaut['global_link_doc_page_reset_password'] = 'http://iamkarlsaycon.com/how-to-change-my-password.html';
            $defaut['global_link_doc_page_leave_validation'] = 'http://iamkarlsaycon.com/page-leave-requests-validation.html';
            $defaut['global_link_doc_page_login'] = 'http://iamkarlsaycon.com/page-login-to-the-application.html';
            $defaut['global_link_doc_page_create_user'] = 'http://iamkarlsaycon.com/page-create-a-new-user.html';
            $defaut['global_link_doc_page_list_users'] = 'http://iamkarlsaycon.com/page-list-of-users.html';
            $defaut['global_link_doc_page_list_employees'] = 'http://iamkarlsaycon.com/page-list-of-employees.html';
            if (array_key_exists($page, $defaut)) {
                return "";
            } else {
                return "http://iamkarlsaycon.com/";
            }
        }
    }

}
