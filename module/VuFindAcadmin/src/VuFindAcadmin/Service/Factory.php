<?php
/**
 * Factory for various top-level VuFind services.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2014.
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
 * @package  Service
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace VuFindAcadmin\Service;
use Zend\ServiceManager\ServiceManager;

/**
 * Factory for various top-level VuFind services.
 *
 * @category VuFind
 * @package  Service
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 *
 * @codeCoverageIgnore
 */
class Factory
{
	
	 /**
     * Construct the Db\Table Plugin Manager.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFind\Db\Table\PluginManager
     */
    public static function getDbTablePluginManager(ServiceManager $sm)
    {
        return static::getGenericPluginManager($sm, 'Db\Table');
    }


   /**
     * Construct the date converter.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    public static function getDbAdapter(ServiceManager $sm)
    {
        return $sm->get('VuFindAcadmin\DbAdapterFactory')->getAdapter();
    }

    /**
     * Construct the date converter.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFindAcadmin\Db\AdapterFactory
     */
    public static function getDbAdapterFactory(ServiceManager $sm)
    {
        return new \VuFindAcadmin\Db\AdapterFactory(
            $sm->get('VuFindAcadmin\Config')->get('config')
        );
    }


/**
     * Generic plugin manager factory (support method).
     *
     * @param ServiceManager $sm Service manager.
     * @param string         $ns VuFind namespace containing plugin manager
     *
     * @return object
     */
    public static function getGenericPluginManager(ServiceManager $sm, $ns)
    {
        $className = 'VuFindAcadmin\\' . $ns . '\PluginManager';
        $configKey = strtolower(str_replace('\\', '_', $ns));
        $config = $sm->get('Config');
        return new $className(
            new \Zend\ServiceManager\Config(
                $config['vufindacadmin']['plugin_managers'][$configKey]
            )
        );
    }
    
    /**
     * Construct the config manager.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFind\Config\PluginManager
     */
    public static function getConfig(ServiceManager $sm)
    {
        $config = $sm->get('Config');
        return new \VuFindAcadmin\Config\PluginManager(
            new \Zend\ServiceManager\Config($config['vufindacadmin']['config_reader'])
        );
    }
    
    /**
     * Construct the ILS connection.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return \VuFind\ILS\Connection
     */
    public static function getILSConnection(ServiceManager $sm)
    {
     /*   $catalog = new \VuFindAcAdmin\ILS\Connection(
            $sm->get('VuFind\Config')->get('config')->Catalog,
            $sm->get('VuFind\ILSDriverPluginManager'),
            $sm->get('VuFind\Config')
        );
        return $catalog->setHoldConfig($sm->get('VuFind\ILSHoldSettings'));
        */
    }
   
}
