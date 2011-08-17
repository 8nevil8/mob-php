<?
/**
 * Simple class that holds debug state across application
 *
 * @author nevil
 */
class Debug {
    private static $enabled = false;

    /**
     * Enable/disable debug.
     * @param bool $enabled 
     */
    public static function setEnabled($enabled = true) {
        self::$enabled = $enabled;
    }

    /**
     * True if debug is enabled, false otherwise.
     * @return bool 
     */
    public static function isEnabled() {
        return self::$enabled;
    }

    /**
     * Prints debug message
     * @param string $message 
     */
    public static function printMessage($message) {
        if (Debug::isEnabled()) {
            echo "<div style='color:orange'>$message</div>";
        }
    }
}
?>
