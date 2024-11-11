<?php

namespace Oopress\Menus;

use Psr\SimpleCache\CacheInterface;

class Fetcher {
    private $menuId;
    private $items;
    private $cache;

    public function __construct($menuId, CacheInterface|bool $cache = false) {
        $this->menuId = $menuId;
        $this->items = [];
        $this->cache = $cache;
    }

    public function fetch()
    {
        $cache_key = 'menu_' . $this->menuId;

        if ($this->cache && $this->cache->has($cache_key)) {
            $this->items = $this->cache->get($cache_key);
        }

        $menuItems = wp_get_nav_menu_items($this->menuId);
        $this->items = $this->organizeItems($menuItems);
        if ($this->cache) {
            $this->cache->set($cache_key, $this->items);
        }
        
        return $this;
    }

    private function organizeItems($menuItems, $parent_id = 0) {
        $organizedItems = [];
        foreach ($menuItems as $item) {
            if ($item->menu_item_parent == $parent_id) {
                $children = $this->organizeItems($menuItems, $item->ID);
                if ($children) {
                    $item->children = $children;
                }
                $organizedItems[] = $item;
            }
        }
        return $organizedItems;
    }

    public function getItems(bool $asJson = false) {
        if ($asJson) {
            return json_encode($this->items);
        }
        return json_decode(json_encode($this->items), true);
    }

    public function render($templateName) {
        // Pass menu items to the template part
        set_query_var('menuItems', $this->items);
        get_template_part($templateName);
    }
}
