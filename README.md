# Oopress Menus

Retrieve WP menus as pure data, as array or JSON. Also allows the data to be passed to a particular template in Wordpress.

![OOPress](https://raw.githubusercontent.com/cmatosbc/oopress-cache/refs/heads/main/img/one.jpg)

## Usage

Usage is pretty simple. You need to instantiate the class with the menu ID or slug, then use the method fetch() to access the data. Further, the method getItems() may return the structured menu data as array or JSON (using true). Finally, the method render() passes the menu data through a template part, rendering it (in the template, the menu data can be accessed from $args['data']).

```php
// Create the instance for "menu-1" menu
$menuData = (new \Oopress\Menus\Fetcher('testing'))
            ->fetch() // Fetches menu items data
            ->getItems(true); // Returns the data in JSON (or array if not TRUE)
```
