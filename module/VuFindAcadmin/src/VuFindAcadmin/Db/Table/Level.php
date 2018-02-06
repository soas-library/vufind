<?php
/**
 * Table Definition for Profile
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
 * @category VuFindAcadmin
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://VuFindAcadmin.org Main Site
 */
namespace VuFindAcadmin\Db\Table;
use Zend\Db\Sql\Expression;

/**
 * Table Definition for Level
 *
 * @category VuFindAcadmin
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://VuFindAcadmin.org Main Site
 */
class Level extends Gateway
{    
    public function __construct()
    {
        parent::__construct('level', 'VuFindAcadmin\Db\Row\Level');
    }

       /**
     * Get all classmarks.
     *
     * @return array
     */
    public function getAll() {

    	$callback = function ($select) {};
    	return $this->select($callback);
    }

    public function getByID($id_level)
    {
        $callback =  function ($select) use ($id_level) {
    		$select->where->equalTo('id_level',$id_level);
    	};
    	return $this->select($callback)->current();
    }

}
