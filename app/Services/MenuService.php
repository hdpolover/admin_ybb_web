<?php

namespace App\Services;

class MenuService
{
    /**
     * Define menu structure for different user roles
     */
    private static $menuStructure = [
        'admin' => [
            'super' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'active_patterns' => ['/dashboard']
                ],
                [
                    'label' => 'Program Management',
                    'icon' => 'fas fa-cogs',
                    'children' => [
                        [
                            'label' => 'Programs',
                            'url' => '/programs',
                            'icon' => 'fas fa-list',
                            'active_patterns' => ['/programs']
                        ],
                        [
                            'label' => 'Program Categories',
                            'url' => '/program-categories',
                            'icon' => 'fas fa-tags',
                            'active_patterns' => ['/program-categories']
                        ],
                        [
                            'label' => 'Program Photos',
                            'url' => '/program-photos',
                            'icon' => 'fas fa-images',
                            'active_patterns' => ['/program-photos']
                        ],
                        [
                            'label' => 'Program Documents',
                            'url' => '/program-documents',
                            'icon' => 'fas fa-file-alt',
                            'active_patterns' => ['/program-documents']
                        ],
                        [
                            'label' => 'Program Schedules',
                            'url' => '/program-schedules',
                            'icon' => 'fas fa-calendar',
                            'active_patterns' => ['/program-schedules']
                        ],
                        [
                            'label' => 'Program Rundowns',
                            'url' => '/program-rundowns',
                            'icon' => 'fas fa-clock',
                            'active_patterns' => ['/program-rundowns']
                        ]
                    ]
                ],
                [
                    'label' => 'Participants',
                    'url' => '/participants',
                    'icon' => 'fas fa-users',
                    'active_patterns' => ['/participants']
                ],
                [
                    'label' => 'Abstracts & Papers',
                    'icon' => 'fas fa-file-text',
                    'children' => [
                        [
                            'label' => 'Abstract Papers',
                            'url' => '/abstract-papers',
                            'icon' => 'fas fa-file',
                            'active_patterns' => ['/abstract-papers']
                        ],
                        [
                            'label' => 'Abstract Topics',
                            'url' => '/abstract-topics',
                            'icon' => 'fas fa-list-ul',
                            'active_patterns' => ['/abstract-topics']
                        ],
                        [
                            'label' => 'Abstract Settings',
                            'url' => '/abstract-settings',
                            'icon' => 'fas fa-cog',
                            'active_patterns' => ['/abstract-settings']
                        ],
                        [
                            'label' => 'Abstract Reviewers',
                            'url' => '/abstract-reviewers',
                            'icon' => 'fas fa-user-check',
                            'active_patterns' => ['/abstract-reviewers']
                        ]
                    ]
                ],
                [
                    'label' => 'Payments',
                    'icon' => 'fas fa-credit-card',
                    'children' => [
                        [
                            'label' => 'Payment Management',
                            'url' => '/payments',
                            'icon' => 'fas fa-money-bill',
                            'active_patterns' => ['/payments']
                        ],
                        [
                            'label' => 'Payment Methods',
                            'url' => '/payment-methods',
                            'icon' => 'fas fa-credit-card',
                            'active_patterns' => ['/payment-methods']
                        ],
                        [
                            'label' => 'Program Payments',
                            'url' => '/program-payments',
                            'icon' => 'fas fa-receipt',
                            'active_patterns' => ['/program-payments']
                        ]
                    ]
                ],
                [
                    'label' => 'Announcements',
                    'url' => '/announcements',
                    'icon' => 'fas fa-bullhorn',
                    'active_patterns' => ['/announcements']
                ],
                [
                    'label' => 'Ambassadors',
                    'url' => '/ambassadors',
                    'icon' => 'fas fa-user-tie',
                    'active_patterns' => ['/ambassadors']
                ],
                [
                    'label' => 'FAQs',
                    'url' => '/faqs',
                    'icon' => 'fas fa-question-circle',
                    'active_patterns' => ['/faqs']
                ],
                [
                    'label' => 'Settings',
                    'icon' => 'fas fa-cogs',
                    'children' => [
                        [
                            'label' => 'Admin Management',
                            'url' => '/admin',
                            'icon' => 'fas fa-user-shield',
                            'active_patterns' => ['/admin']
                        ],
                        [
                            'label' => 'Web Settings',
                            'url' => '/main-config',
                            'icon' => 'fas fa-globe',
                            'active_patterns' => ['/main-config']
                        ],
                        [
                            'label' => 'Program Details',
                            'url' => '/program-details',
                            'icon' => 'fas fa-info-circle',
                            'active_patterns' => ['/program-details']
                        ]
                    ]
                ]
            ],
            'program_admin' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'active_patterns' => ['/dashboard']
                ],
                [
                    'label' => 'Participants',
                    'url' => '/participants',
                    'icon' => 'fas fa-users',
                    'active_patterns' => ['/participants']
                ],
                [
                    'label' => 'Payments',
                    'icon' => 'fas fa-credit-card',
                    'children' => [
                        [
                            'label' => 'Payment Management',
                            'url' => '/payments',
                            'icon' => 'fas fa-money-bill',
                            'active_patterns' => ['/payments']
                        ],
                        [
                            'label' => 'Program Payments',
                            'url' => '/program-payments',
                            'icon' => 'fas fa-receipt',
                            'active_patterns' => ['/program-payments']
                        ]
                    ]
                ],
                [
                    'label' => 'Program Settings',
                    'icon' => 'fas fa-cogs',
                    'children' => [
                        [
                            'label' => 'Program Details',
                            'url' => '/program-details',
                            'icon' => 'fas fa-info-circle',
                            'active_patterns' => ['/program-details']
                        ],
                        [
                            'label' => 'Program Documents',
                            'url' => '/program-documents',
                            'icon' => 'fas fa-file-alt',
                            'active_patterns' => ['/program-documents']
                        ],
                        [
                            'label' => 'Program Schedules',
                            'url' => '/program-schedules',
                            'icon' => 'fas fa-calendar',
                            'active_patterns' => ['/program-schedules']
                        ]
                    ]
                ]
            ],
            'editor' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'active_patterns' => ['/dashboard']
                ],
                [
                    'label' => 'Announcements',
                    'url' => '/announcements',
                    'icon' => 'fas fa-bullhorn',
                    'active_patterns' => ['/announcements']
                ],
                [
                    'label' => 'FAQs',
                    'url' => '/faqs',
                    'icon' => 'fas fa-question-circle',
                    'active_patterns' => ['/faqs']
                ],
                [
                    'label' => 'Content Management',
                    'icon' => 'fas fa-edit',
                    'children' => [
                        [
                            'label' => 'Program Photos',
                            'url' => '/program-photos',
                            'icon' => 'fas fa-images',
                            'active_patterns' => ['/program-photos']
                        ],
                        [
                            'label' => 'Program Testimonies',
                            'url' => '/program-testimonies',
                            'icon' => 'fas fa-quote-left',
                            'active_patterns' => ['/program-testimonies']
                        ]
                    ]
                ]
            ],
            'moderator' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'active_patterns' => ['/dashboard']
                ],
                [
                    'label' => 'Participants',
                    'url' => '/participants',
                    'icon' => 'fas fa-users',
                    'active_patterns' => ['/participants']
                ],
                [
                    'label' => 'Abstracts & Papers',
                    'icon' => 'fas fa-file-text',
                    'children' => [
                        [
                            'label' => 'Abstract Papers',
                            'url' => '/abstract-papers',
                            'icon' => 'fas fa-file',
                            'active_patterns' => ['/abstract-papers']
                        ],
                        [
                            'label' => 'Abstract Reviewers',
                            'url' => '/abstract-reviewers',
                            'icon' => 'fas fa-user-check',
                            'active_patterns' => ['/abstract-reviewers']
                        ]
                    ]
                ]
            ]
        ],
        'reviewer' => [
            'reviewer' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/reviewer/dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'active_patterns' => ['/reviewer/dashboard']
                ],
                [
                    'label' => 'My Reviews',
                    'url' => '/reviewer/reviews',
                    'icon' => 'fas fa-clipboard-list',
                    'active_patterns' => ['/reviewer/reviews']
                ],
                [
                    'label' => 'Assigned Papers',
                    'url' => '/reviewer/papers',
                    'icon' => 'fas fa-file-alt',
                    'active_patterns' => ['/reviewer/papers']
                ],
                [
                    'label' => 'Review Guidelines',
                    'url' => '/reviewer/guidelines',
                    'icon' => 'fas fa-book',
                    'active_patterns' => ['/reviewer/guidelines']
                ]
            ]
        ]
    ];

    /**
     * Get menu items for a specific user type and role
     * 
     * @param string $userType Either 'admin' or 'reviewer'
     * @param string $role The specific role within the user type
     * @return array Menu items array
     */
    public static function getMenuForRole($userType, $role)
    {
        if (!isset(self::$menuStructure[$userType][$role])) {
            return [];
        }

        return self::$menuStructure[$userType][$role];
    }

    /**
     * Get all available roles for a user type
     * 
     * @param string $userType Either 'admin' or 'reviewer'
     * @return array Available roles
     */
    public static function getAvailableRoles($userType)
    {
        if (!isset(self::$menuStructure[$userType])) {
            return [];
        }

        return array_keys(self::$menuStructure[$userType]);
    }

    /**
     * Check if a user has access to a specific menu item
     * 
     * @param string $userType
     * @param string $role
     * @param string $url
     * @return bool
     */
    public static function hasAccess($userType, $role, $url)
    {
        $menuItems = self::getMenuForRole($userType, $role);
        return self::checkMenuAccess($menuItems, $url);
    }

    /**
     * Recursively check if URL exists in menu items
     * 
     * @param array $menuItems
     * @param string $url
     * @return bool
     */
    private static function checkMenuAccess($menuItems, $url)
    {
        foreach ($menuItems as $item) {
            // Check direct URL match
            if (isset($item['url']) && $item['url'] === $url) {
                return true;
            }

            // Check active patterns
            if (isset($item['active_patterns'])) {
                foreach ($item['active_patterns'] as $pattern) {
                    if (strpos($url, $pattern) === 0) {
                        return true;
                    }
                }
            }

            // Check children
            if (isset($item['children']) && self::checkMenuAccess($item['children'], $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get menu with active states based on current URL
     * 
     * @param string $userType
     * @param string $role
     * @param string $currentUrl
     * @return array
     */
    public static function getMenuWithActiveStates($userType, $role, $currentUrl)
    {
        $menuItems = self::getMenuForRole($userType, $role);
        return self::setActiveStates($menuItems, $currentUrl);
    }

    /**
     * Recursively set active states for menu items
     * 
     * @param array $menuItems
     * @param string $currentUrl
     * @return array
     */
    private static function setActiveStates($menuItems, $currentUrl)
    {
        foreach ($menuItems as &$item) {
            $item['is_active'] = false;
            $item['has_active_child'] = false;

            // Check if current item is active
            if (isset($item['url']) && $item['url'] === $currentUrl) {
                $item['is_active'] = true;
            } elseif (isset($item['active_patterns'])) {
                foreach ($item['active_patterns'] as $pattern) {
                    if (strpos($currentUrl, $pattern) === 0) {
                        $item['is_active'] = true;
                        break;
                    }
                }
            }

            // Check children
            if (isset($item['children'])) {
                $item['children'] = self::setActiveStates($item['children'], $currentUrl);
                
                // Check if any child is active
                foreach ($item['children'] as $child) {
                    if ($child['is_active'] || $child['has_active_child']) {
                        $item['has_active_child'] = true;
                        break;
                    }
                }
            }
        }

        return $menuItems;
    }

    /**
     * Get breadcrumb for current URL
     * 
     * @param string $userType
     * @param string $role
     * @param string $currentUrl
     * @return array
     */
    public static function getBreadcrumb($userType, $role, $currentUrl)
    {
        $menuItems = self::getMenuForRole($userType, $role);
        $breadcrumb = [];
        
        self::findBreadcrumb($menuItems, $currentUrl, $breadcrumb);
        
        return $breadcrumb;
    }

    /**
     * Recursively find breadcrumb path
     * 
     * @param array $menuItems
     * @param string $currentUrl
     * @param array &$breadcrumb
     * @param array $path
     * @return bool
     */
    private static function findBreadcrumb($menuItems, $currentUrl, &$breadcrumb, $path = [])
    {
        foreach ($menuItems as $item) {
            $currentPath = array_merge($path, [$item]);

            // Check if current item matches
            if (isset($item['url']) && $item['url'] === $currentUrl) {
                $breadcrumb = $currentPath;
                return true;
            }

            // Check active patterns
            if (isset($item['active_patterns'])) {
                foreach ($item['active_patterns'] as $pattern) {
                    if (strpos($currentUrl, $pattern) === 0) {
                        $breadcrumb = $currentPath;
                        return true;
                    }
                }
            }

            // Check children
            if (isset($item['children']) && self::findBreadcrumb($item['children'], $currentUrl, $breadcrumb, $currentPath)) {
                return true;
            }
        }

        return false;
    }
}
