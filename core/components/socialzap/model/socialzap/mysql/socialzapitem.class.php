<?php
/**
 * @package socialzap
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\', '/') . '/socialzapitem.class.php');
class SocialZapItem_mysql extends SocialZapItem {}
?>