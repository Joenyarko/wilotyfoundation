<?php
// config/Cache.php
// File-based output caching engine for high-traffic optimization

class Cache {
    private static $cache_dir = __DIR__ . '/../cache/';
    
    /**
     * Start the cache. If a valid cache exists, it outputs it and stops execution.
     * @param string $page_id Unique identifier for the page (e.g., 'home', 'event_5')
     * @param int $duration Time in seconds to keep the cache alive (default: 300 = 5 mins)
     * @return string|false The cache file path to save later, or false if caching is disabled
     */
    public static function start($page_id, $duration = 300) {
        if (!is_dir(self::$cache_dir)) {
            mkdir(self::$cache_dir, 0755, true);
        }

        // Disable cache if admin is logged in so they see live updates
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            return false;
        }

        $cache_file = self::$cache_dir . md5($page_id) . '.html';

        // Check if cache exists and is fresh
        if (file_exists($cache_file) && (time() - $duration < filemtime($cache_file))) {
            echo file_get_contents($cache_file);
            echo "\n<!-- Cached Snapshot: " . date('Y-m-d H:i:s', filemtime($cache_file)) . " | Refresh in: " . ($duration - (time() - filemtime($cache_file))) . "s -->";
            exit; // Stop executing the rest of the PHP script
        }
        
        // Start capturing output
        ob_start();
        return $cache_file;
    }

    /**
     * End the cache capturing and save to file.
     * @param string|false $cache_file The path returned from start()
     */
    public static function end($cache_file) {
        if ($cache_file) {
            $content = ob_get_clean();
            // Save to file
            file_put_contents($cache_file, $content);
            // Output to user
            echo $content;
        }
    }
}
