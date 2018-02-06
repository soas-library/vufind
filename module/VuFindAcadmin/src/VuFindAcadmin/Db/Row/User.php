<?php
/**
 * Row Definition for record
 *
 * PHP version 5
 *
 * @category VuFindAcadmin
 * @package  Db_Table
 * @author   Scanbit
 */
namespace VuFindAcadmin\Db\Row;

/**
 * Row Definition for user
 *
 * @category VuFind
 * @package  Db_Row
 * @author   Markus Beh <markus.beh@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class User extends RowGateway
{
    /**
     * Constructor
     *
     * @param \Zend\Db\Adapter\Adapter $adapter Database adapter
     */
    public function __construct($adapter)
    {
        parent::__construct('id', 'user', $adapter);
    }
}
