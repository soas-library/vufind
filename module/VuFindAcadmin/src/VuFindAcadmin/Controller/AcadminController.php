<?php
/**
 * Admin Controller
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFindAcadmin\Controller;

$GLOBALS['OTHER_PROFILE'] = 1;
$GLOBALS['LIBRARIAN_PROFILE'] = 2;
$GLOBALS['ADMIN_PROFILE'] = 3;
$GLOBALS['ADMIN_LOCATION_PROFILE'] = 4;

/**
 * Class controls VuFind administration.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class AcadminController extends AbstractAcadmin
{
    /**
     * Display disabled message.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function disabledAction()
    {
        return $this->createViewModel();
    }

    /**
     * Admin home.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {
	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE']  || $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }  
         $view = $this->createViewModel();
         return $view;        
    }

    
}
