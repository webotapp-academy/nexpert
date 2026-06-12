<!-- Auto-trigger cron jobs silently in background -->
<img src="<?php echo BASE_PATH; ?>/cron/trigger.php" style="display:none;" alt="" width="1" height="1" />
</body>
</html>
<?php
// Alternative: PHP include method (more reliable)
@include_once __DIR__ . '/../cron/trigger.php';
?>
