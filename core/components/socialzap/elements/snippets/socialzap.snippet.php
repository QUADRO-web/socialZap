<?php
/*
 * socialZap
 *
 * Snippet to show posts
 *
 * Usage examples:
 * [[socialZap? &tpl=`yourTpl`]]
 *
 * @author Jan Dähne <jan.daehne@quadro-system.de>
 */

$corePath = $modx->getOption('socialzap.core_path', null, $modx->getOption('core_path') . 'components/socialzap/');
$socialzap = $modx->getService('socialzap', 'SocialZap', $corePath . 'model/socialzap/', array(
    'core_path' => $corePath
));

// properties
$tpl = $modx->getOption('tpl', $scriptProperties, 'socialZapTpl', true);
$limit = $modx->getOption('limit', $scriptProperties, 12, true);
$offset = $modx->getOption('offset', $scriptProperties, 0, true);
$sortby = $modx->getOption('sortby', $scriptProperties, 'date', true);
$sortdir = $modx->getOption('sortdir', $scriptProperties, 'desc', true);
$filterUser = $modx->getOption('filterUser', $scriptProperties);
$filterContent = $modx->getOption('filterContent', $scriptProperties);
$filterSource = $modx->getOption('filterSource', $scriptProperties);
$cache = $modx->getOption('cache', $scriptProperties, true, true);
$cacheTime = $modx->getOption('cacheTime', 3600, true);
$cacheKey = $modx->getOption('cacheKey', $scriptProperties, 'socialZap', true);


// get items
$items = $socialzap->getItems($limit, $offset, $sortby, $sortdir, $filterUser, $filterContent, $filterSource, array(
    'cache' => $cache,
    'time' => $cacheTime,
    'key' => $cacheKey,
));

$output = '';

if (is_array($items)) {
    foreach ($items as $item) {
        $output .= $modx->getChunk($tpl, $item);
    }
}


return $output;