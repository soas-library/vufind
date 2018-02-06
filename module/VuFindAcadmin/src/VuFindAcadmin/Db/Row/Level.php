<?php
/**
 * Row Definition for Profile
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
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://VuFindAcadmin.org Main Site
 */
namespace VuFindAcadmin\Db\Row;
use VuFindAcadmin\Date\Converter as DateConverter,
    VuFindAcadmin\Exception\Date as DateException,
    VuFindAcadmin\Exception\LoginRequired as LoginRequiredException;

/**
 * Row Definition for Level
 *
 * @category VuFindAcadmin
 * @package  Db_Row
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://VuFindAcadmin.org Main Site
 */
class Level extends RowGateway implements \VuFindAcadmin\Db\Table\DbTableAwareInterface
{
    use \VuFindAcadmin\Db\Table\DbTableAwareTrait;

    /**
     * Constructor
     *
     * @param \Zend\Db\Adapter\Adapter $adapter Database adapter
     */
    public function __construct($adapter)
    {
        parent::__construct('id_level', 'id_level', $adapter);
    }

}
