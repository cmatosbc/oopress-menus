<?php

namespace Oopress\Menus;

use Psr\SimpleCache\CacheInterface;

/**
 * Fetches and organizes menu items from WordPress.
 *
 * This class provides methods to retrieve menu items, organize them into a hierarchical structure, and render them using a template.
 * It also leverages a cache to improve performance.
 * 
 * @author Carlos Artur Matos
 * @version 1.0.0
 */
class Fetcher {
    /**
     * The ID of the menu to fetch.
     *
     * @var int
     */
    private $menuId;

    /**
     * The fetched and organized menu items.
     *
     * @var array
     */
    private $items;

    /**
     * The cache interface for storing and retrieving menu items.
     *
     * @var CacheInterface|bool
     */
    private $cache;

    /**
     * Constructs a new Fetcher instance.
     *
     * @param int $menuId The ID of the menu to fetch.
     * @param CacheInterface|bool $cache Optional cache interface for storing and retrieving menu items.
     */
    public function __construct($menuId, CacheInterface|bool $cache = false)
    {
        $this->menuId = $menuId;
        $this->items = [];
        $this->cache = $cache;
    }

    /**
     * Fetches and organizes menu items.
     *
     * This method retrieves menu items from WordPress, organizes them into a hierarchical structure, and optionally stores them in the cache.
     *
     * @return Fetcher The current Fetcher instance.
     */
    public function fetch()
    {
        $cache_key = 'menu_' . $this->menuId;

        if ($this->cache && $this->cache->has($cache_key)) {
            $this->items = $this->cache->get($cache_key);
        } else {
            $menuItems = wp_get_nav_menu_items($this->menuId);
            $this->items = $this->organizeItems($menuItems);
            if ($this->cache) {
                $this->cache->set($cache_key, $this->items);
            }
        }

        return $this;
    }

    /**
     * Organizes menu items into a hierarchical structure.
     *
     * @param array $menuItems The array of menu items.
     * @param int $parent_id The ID of the parent menu item.
     * @return array The organized menu items.
     */
    private function organizeItems($menuItems, $parent_id = 0)
    {
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

    /**
     * Gets the menu items.
     *
     * @param bool $asJson Whether to return the menu items as a JSON string.
     * @return array|string The menu items as an array or JSON string.
     */
    public function getItems(bool $asJson = false)
    {
        if ($asJson) {
            return json_encode($this->items);
        }
        return json_decode(json_encode($this->items), true);
    }

    /**
     * Renders the menu items using a template part.
     *
     * @param string $templateName The name of the template part.
     */
    public function render($templateName)
    {
        // Pass menu items to the template part
        set_query_var('menuItems', $this->items);
        get_template_part($templateName);
    }
}
