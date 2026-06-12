<?php
// Universal Environment Variable Loader
// Works with both Replit Secrets and .env file for local development

class UniversalEnv {
    private static $loaded = false;
    private static $envVars = [];
    
    // Load environment variables from .env file and merge with system env
    public static function load() {
        if (self::$loaded) {
            return;
        }
        
        // First, check if we're in Replit (environment variables available)
        $replitEnv = getenv('REPL_ID');
        
        // Try to load from .env file (for local development like XAMPP)
        $envFile = __DIR__ . '/../../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Store in our array
                    self::$envVars[$key] = $value;
                    
                    // Also set as environment variable if not already set
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }
        
        self::$loaded = true;
    }
    
    // Get environment variable (checks both .env and system env)
    public static function get($key, $default = null) {
        self::load();
        
        // First try system environment (Replit Secrets)
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Then try our loaded .env values
        if (isset(self::$envVars[$key])) {
            return self::$envVars[$key];
        }
        
        // Return default if not found
        return $default;
    }
    
    // Check if a variable exists
    public static function has($key) {
        self::load();
        return getenv($key) !== false || isset(self::$envVars[$key]);
    }
}
