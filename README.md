# WP Notices

Easy to use notices management library for WordPress that implements [WordPress notices](https://developer.wordpress.org/reference/hooks/admin_notices/)

The library creates all the necessary stuff and ajax handling for dismissing the notices and also offers manual
dismissing through code.

## Quick Start

### 1. How to install the library

```
composer require ignitekit/wp-notices
```

### 2. How to use `NoticesManager` class

The class `IgniteKit\WP\Notices\NoticesManager` is the one that need to be used in order to add new notices as follows:

```php
use IgniteKit\WP\Notices\NoticesManager;

class My_Plugin_Bootstrap() {

    private $notices_manager;
    
    public function __construct() {
        $this->notices_manager = new NoticesManager('myplugin');
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
    
        // Eg. Add error notice if Woocommerce is not installed.
        if( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            
            // Returns instance of IgniteKit\WP\Notices\Notice
            $notice = $this->notices_manager->add_error( 'missing_wc', '<h3>WooCommerce not installed</h3><p>Please install WooCommerce in order to use My Plugin</p>', NoticesManager::DISMISS_FOREVER );   
            
            // Methods available in Notice
            var_dump($notice->id); // The notice ID
            var_dump($notice->is_dismissed()); // True or false
            var_dump($notice->dismiss()); // Dismisses the notice
            var_dump($notice->reset()); // Resets the notice. Removes the dismissed status.
        }
        
        // Somewhere later you can use this to retrieve the error notice you added before...
        // ...apply some logic.
        $notice = $this->notices_manager->get_notice('missing_wc', 'error');
        $notice->reset(); // Then maybe reset it?
        $notice->dismiss(); // Somewhere later, maybe dismiss again?
    }
}
```

### 3. List of methods available in `NoticesManager` for adding notices.

There are several methods available in the `IgniteKit\WP\Notices\NoticesManager` for adding notices.

Every mehtod of those returns `IgniteKit\WP\Notices\Notice` instance. This is basically the notice class.

#### Method add_success()

```php
/**
 * Add success notice. Displayed with greeen border.
 *
 * @param $key
 * @param $message - html of the notice
 * @param string|int $expiry - Specifes how much time the notice stays disabled. 
 *
 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
 *
 * @return Notice
 */
public function add_success( $key, $message, $expiry );
```

#### Method add_error()

```php
/**
 * Add error notice. Displayed with red border.
 *
 * @param string $key - unique identifier
 * @param string $message - html of the notice
 * @param string|int $expiry - Specifes how much time the notice stays disabled. 
 *
 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
 *
 * @return Notice
 */
public function add_error( $key, $message, $expiry );
```

#### Method add_info()

```php
/**
 * Add info notice. Displayed with blue border.
 *
 * @param string $key - unique identifier
 * @param string $message - html of the notice
 * @param string|int $expiry - Specifes how much time the notice stays disabled. 
 *
 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
 *
 * @return Notice
 */
public function add_info( $key, $message, $expiry );
```

#### Method add_warning()

```php
/**
 * Add warning notice. Displayed with orange border.
 *
 * @param string $key - unique identifier
 * @param string $message - html of the notice
 * @param string|int $expiry - Specifes how much time the notice stays disabled. 
 *
 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
 *
 * @return Notice
 */
public function add_warning( $key, $message, $expiry );
```

#### Method add_custom()

```php
/**
 * Add custom notice. Displayed with gray border.
 *
 * @param string $key - unique identifier
 * @param string $message - html of the notice
 * @param string|int $expiry - Specifes how much time the notice stays disabled. 
 *
 * Expiry parameter can be: NoticesManager::DISMISS_FOREVER, NoticesManager::DISMISS_DISABLED or number of seconds)
 *
 * @return Notice
 */
public function add_custom( $key, $message, $expiry );
```

#### Method get_notice_by_id()

```php
/**
 * Return the notice object
 *
 * @param $id
 *
 * @return Notice|null
 */
public function get_notice_by_id( $id )
```

#### Method get_notice()

```php
/**
 * Return notice by key and type
 *
 * @param $key
 * @param $type
 *
 * @return Notice|null
 */
public function get_notice( $key, $type );
```

### 4. List of methods available in `Notice` instance that is returned after notice is added.

There are several methods available in the `IgniteKit\WP\Notices\Notice` class. You can manually dismiss or reset the
notice, also check if the notice is dismissed.

#### Method is_dismissed()

```php
/**
 * Check if notice is dismissed.
 *
 * @return bool
 */
public function is_dismissed()
```

#### Method dismiss()

```php
/**
 * Dismisses the notice
 */
public function dismiss()
```

#### Method reset()

```php
/**
 * Removes notice dismissal flag. After this call the notice is not dismissed anymore.
 */
public function reset()
```

## License

```
Copyright (C) 2021 Darko Gjorgjijoski (https://darkog.com)

This file is part of WP Notices

WP Notices is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

WP Notices is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WP Notices. If not, see <https://www.gnu.org/licenses/>.
```