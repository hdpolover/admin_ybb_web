<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table = 'menu_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'label', 'icon', 'url', 'route_name', 'parent_id', 'sort_order',
        'is_active', 'required_permission', 'badge_text', 'badge_color'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'label' => 'required|min_length[2]|max_length[100]',
        'sort_order' => 'permit_empty|integer'
    ];

    /**
     * Get menu items for user based on role permissions
     */
    public function getMenuForUser(int $roleId): array
    {
        $db = \Config\Database::connect();
        
        // Get all menu items with permission check
        $query = $db->query("
            SELECT mi.*,
                CASE 
                    WHEN mi.required_permission IS NULL THEN 1
                    WHEN EXISTS (
                        SELECT 1 FROM role_permissions rp 
                        INNER JOIN permissions p ON p.id = rp.permission_id 
                        WHERE rp.role_id = ? AND p.name = mi.required_permission AND p.is_active = 1
                    ) THEN 1
                    ELSE 0
                END as has_permission
            FROM menu_items mi
            WHERE mi.is_active = 1
            ORDER BY mi.sort_order ASC, mi.label ASC
        ", [$roleId]);
        
        $menuItems = $query->getResult();
        
        // Filter items with permissions and build hierarchy
        return $this->buildMenuHierarchy($menuItems);
    }

    /**
     * Build hierarchical menu structure
     */
    private function buildMenuHierarchy(array $menuItems): array
    {
        $hierarchy = [];
        $itemsById = [];
        
        // First pass: index by ID and filter permitted items
        foreach ($menuItems as $item) {
            if ($item->has_permission) {
                $item->children = [];
                $itemsById[$item->id] = $item;
            }
        }
        
        // Second pass: build hierarchy
        foreach ($itemsById as $item) {
            if ($item->parent_id === null) {
                $hierarchy[] = $item;
            } else if (isset($itemsById[$item->parent_id])) {
                $itemsById[$item->parent_id]->children[] = $item;
            }
        }
        
        return $hierarchy;
    }

    /**
     * Get all menu items with hierarchy info
     */
    public function getMenuItemsWithHierarchy(): array
    {
        $items = $this->orderBy('sort_order', 'ASC')
                     ->orderBy('label', 'ASC')
                     ->findAll();
        
        return $this->buildFullHierarchy($items);
    }

    /**
     * Build full hierarchy for admin management
     */
    private function buildFullHierarchy(array $items): array
    {
        $hierarchy = [];
        $itemsById = [];
        
        // Index by ID
        foreach ($items as $item) {
            $item->children = [];
            $item->level = 0;
            $itemsById[$item->id] = $item;
        }
        
        // Build hierarchy and calculate levels
        foreach ($itemsById as $item) {
            if ($item->parent_id === null) {
                $hierarchy[] = $item;
            } else if (isset($itemsById[$item->parent_id])) {
                $item->level = $itemsById[$item->parent_id]->level + 1;
                $itemsById[$item->parent_id]->children[] = $item;
            }
        }
        
        return $this->flattenHierarchy($hierarchy);
    }

    /**
     * Flatten hierarchy for display purposes
     */
    private function flattenHierarchy(array $hierarchy, array &$flattened = []): array
    {
        foreach ($hierarchy as $item) {
            $item->indent = str_repeat('└── ', $item->level);
            $flattened[] = $item;
            
            if (!empty($item->children)) {
                $this->flattenHierarchy($item->children, $flattened);
            }
        }
        
        return $flattened;
    }

    /**
     * Create default menu items
     */
    public function createDefaultMenuItems(): bool
    {
        $defaultMenuItems = [
            // Main navigation
            ['name' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'url' => '/dashboard', 'parent_id' => null, 'sort_order' => 1, 'required_permission' => 'view_dashboard'],
            
            // Participants section
            ['name' => 'participants', 'label' => 'Participants', 'icon' => 'ri-user-line', 'url' => '#', 'parent_id' => null, 'sort_order' => 2, 'required_permission' => 'view_participants'],
            ['name' => 'participants_list', 'label' => 'Participant List', 'icon' => 'ri-list-check', 'url' => '/participants', 'parent_id' => null, 'sort_order' => 3, 'required_permission' => 'view_participants'],
            ['name' => 'participants_export', 'label' => 'Export Participants', 'icon' => 'ri-download-line', 'url' => '/participants/export', 'parent_id' => null, 'sort_order' => 4, 'required_permission' => 'export_participants'],
            
            // Essays & Content
            ['name' => 'essays', 'label' => 'Essays & Content', 'icon' => 'ri-article-line', 'url' => '#', 'parent_id' => null, 'sort_order' => 5, 'required_permission' => 'view_essays'],
            ['name' => 'essays_list', 'label' => 'Essay Reviews', 'icon' => 'ri-file-text-line', 'url' => '/essays', 'parent_id' => null, 'sort_order' => 6, 'required_permission' => 'view_essays'],
            
            // Scoring
            ['name' => 'scoring', 'label' => 'Scoring', 'icon' => 'ri-star-line', 'url' => '/scoring', 'parent_id' => null, 'sort_order' => 7, 'required_permission' => 'view_scores'],
            
            // Ambassadors
            ['name' => 'ambassadors', 'label' => 'Ambassadors', 'icon' => 'ri-user-star-line', 'url' => '/ambassadors', 'parent_id' => null, 'sort_order' => 8, 'required_permission' => 'view_ambassadors'],
            
            // News & Content
            ['name' => 'news', 'label' => 'News & Content', 'icon' => 'ri-news-line', 'url' => '/news', 'parent_id' => null, 'sort_order' => 9, 'required_permission' => 'view_news'],
            
            // Analytics
            ['name' => 'analytics', 'label' => 'Analytics', 'icon' => 'ri-bar-chart-line', 'url' => '/analytics', 'parent_id' => null, 'sort_order' => 10, 'required_permission' => 'view_analytics'],
            
            // System Management
            ['name' => 'system', 'label' => 'System Management', 'icon' => 'ri-settings-line', 'url' => '#', 'parent_id' => null, 'sort_order' => 11, 'required_permission' => 'system_settings'],
            ['name' => 'admin_management', 'label' => 'Admin Management', 'icon' => 'ri-admin-line', 'url' => '/settings/admin-management', 'parent_id' => null, 'sort_order' => 12, 'required_permission' => 'manage_admins'],
            ['name' => 'role_management', 'label' => 'Roles & Permissions', 'icon' => 'ri-user-settings-line', 'url' => '/settings/roles', 'parent_id' => null, 'sort_order' => 13, 'required_permission' => 'manage_roles'],
            ['name' => 'system_settings', 'label' => 'System Settings', 'icon' => 'ri-settings-3-line', 'url' => '/settings/system', 'parent_id' => null, 'sort_order' => 14, 'required_permission' => 'system_settings'],
        ];

        try {
            foreach ($defaultMenuItems as $menuItem) {
                // Check if menu item already exists
                $existing = $this->where('name', $menuItem['name'])->first();
                if (!$existing) {
                    $menuItem['is_active'] = 1;
                    $this->insert($menuItem);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update menu item sort order
     */
    public function updateSortOrder(array $itemOrders): bool
    {
        try {
            foreach ($itemOrders as $itemId => $sortOrder) {
                $this->update($itemId, ['sort_order' => $sortOrder]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get menu breadcrumb
     */
    public function getBreadcrumb(string $currentUrl): array
    {
        $breadcrumb = [];
        $menuItem = $this->where('url', $currentUrl)->first();
        
        if ($menuItem) {
            $breadcrumb[] = $menuItem;
            
            // Walk up the hierarchy
            while ($menuItem->parent_id) {
                $menuItem = $this->find($menuItem->parent_id);
                if ($menuItem) {
                    array_unshift($breadcrumb, $menuItem);
                }
            }
        }
        
        return $breadcrumb;
    }
}