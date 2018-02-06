<?php
namespace VuFindAcadmin\Module\Configuration;

$config = [
    'controllers' => [
        'invokables' => [
            'acadmin' => 'VuFindAcadmin\Controller\AcadminController',
            'acadminlocations' => 'VuFindAcadmin\Controller\LocationsController',
            'acadminaccesstypes' => 'VuFindAcadmin\Controller\AccesstypesController',
            'acadminaccesspermissions' => 'VuFindAcadmin\Controller\AccesspermissionsController',
            'acadminelectronicresources' => 'VuFindAcadmin\Controller\ElectronicresourcesController',
            'locationlist' => 'VuFindAcadmin\Controller\LocationlistController',
            'acadminuserpermissions' => 'VuFindAcadmin\Controller\UserpermissionsController',
            'acadminuserroles' => 'VuFindAcadmin\Controller\UserrolesController',
            'acadminlibrarylocations' => 'VuFindAcadmin\Controller\LibrarylocationsController',
            //'location' => 'VuFindAcadmin\Controller\LocationController',
        ],
    ],
    'router' => [
        'routes' => [
            'acadmin' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/Acadmin',
                    'defaults' => [
                        'controller' => 'Acadmin',
                        'action'     => 'Home',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'locations' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Locations[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLocations',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'location' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Location[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLocations',
                                'action'     => 'location',
                            ]
                        ]
                    ],
                    'classmark' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Classmark[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLocations',
                                'action'     => 'classmark',
                            ]
                        ]
                    ],
                    'note' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Note[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLocations',
                                'action'     => 'note',
                            ]
                        ]
                    ],
                    'maintext' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Maintext[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLocations',
                                'action'     => 'maintext',
                            ]
                        ]
                    ],
                    'userrole' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Userrole[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminUserroles',
                                'action'     => 'userrole',
                            ]
                        ]
                    ],  
                    'userpermission' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Userpermission[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminUserpermissions',
                                'action'     => 'userpermission',
                            ]
                        ]
                    ],  
                    'librarylocation' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Librarylocation[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLibrarylocations',
                                'action'     => 'librarylocation',
                            ]
                        ]
                    ],  
                    'delete' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Delete[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLocations',
                                'action'     => 'delete',
                            ]
                        ]
                    ],
                     
                     'deleteuserrole' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Deleteuserrole[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminUserroles',
                                'action'     => 'deleteuserrole',
                            ]
                        ]
                    ],
                    
                     'deletelibrarylocation' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Deletelibrarylocation[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLibrarylocations',
                                'action'     => 'deletelibrarylocation',
                            ]
                        ]
                    ],

                    'accesstypes' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Accesstypes[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminAccesstypes',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'type' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Type[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminAccesstypes',
                                'action'     => 'type',
                            ]
                        ]
                    ],
                    'accesspermissions' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Accesspermissions[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminAccesspermissions',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'electronicresources' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Electronicresources[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminElectronicresources',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'userroles' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Userroles[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminUserroles',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'librarylocations' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Librarylocations[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminLibrarylocations',
                                'action'     => 'Home',
                            ]
                        ]
                    ], 
                    'userpermissions' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Userpermissions[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminUserpermissions',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'resource' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Resource[/:action]',
                            'defaults' => [
                                'controller' => 'AcadminElectronicresources',
                                'action'     => 'resource',
                            ]
                        ]
                    ],
                ], 
            ],
            'locationlist' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/Locationlist',
                    'defaults' => [
                        'controller' => 'Locationlist',
                        'action'     => 'Home',
                    ]
                ],
                'may_terminate' => true,
                 'child_routes' => [
                    'location' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Location[/:action]',
                            'defaults' => [
                                'controller' => 'Locationlist',
                                'action'     => 'location',
                            ]
                        ]
                    ],
                ],
            ],            
        ],
    ],
    'service_manager' => [
        'allow_override' => true,
        'factories' => [ 
            'VuFindAcadmin\Config' => 'VuFindAcadmin\Service\Factory::getConfig',       
            'VuFindAcadmin\DbAdapter' => 'VuFindAcadmin\Service\Factory::getDbAdapter',
            'VuFindAcadmin\DbAdapterFactory' => 'VuFindAcadmin\Service\Factory::getDbAdapterFactory',
            'VuFindAcadmin\DbTablePluginManager' => 'VuFindAcadmin\Service\Factory::getDbTablePluginManager',
            
        ],
   ],
   
    'vufindacadmin' => [
       // The config reader is a special service manager for loading .ini files:
        'config_reader' => [
            'abstract_factories' => ['VuFindAcadmin\Config\PluginFactory'],
        ],
        // This section contains service manager configurations for all VuFind
        // pluggable components:
        'plugin_managers' => [
            'db_table' => [
                'abstract_factories' => ['VuFindAcadmin\Db\Table\PluginFactory'],
                'factories' => [
                    'resource' => 'VuFind\Db\Table\Factory::getResource',
                ],
                'invokables' => [
                    'location' => 'VuFindAcadmin\Db\Table\Location',
                    'classmark' => 'VuFindAcadmin\Db\Table\Classmark',
                    'user' => 'VuFindAcadmin\Db\Table\User',  
                ],
            ],            
        ],
    ],
    
    
    
];

return $config;
