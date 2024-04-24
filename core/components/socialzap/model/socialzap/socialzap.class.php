<?php
/**
 * @package socialZap
 */
class SocialZap {

    public $modx;
    public $config;

    public $secret;
    public $image_path;
    public $published;

    /**
     * socialZap constructor
     *
     * @param MODX A reference to the MODX instance.
     */
     public function __construct(modX &$modx, array $config = array())
     {
        // init modx
        $this->modx = & $modx;

        // config
        $this->secret = $this->modx->getOption('socialzap.secret');
        $this->image_path = $this->modx->getOption('socialzap.image_path');
        $this->published = $this->modx->getOption('socialzap.published');

        // add addPackage
        $basePath = $this->modx->getOption('socialzap.core_path',$config,$this->modx->getOption('core_path').'components/socialzap/');
        $assetsUrl = $this->modx->getOption('signfy.assets_url',$config,$this->modx->getOption('assets_url').'components/socialzap/');
        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath.'model/',
            'processorsPath' => $basePath.'processors/',
            'templatesPath' => $basePath.'templates/',
            'chunksPath' => $basePath.'elements/chunks/',
            'jsUrl' => $assetsUrl.'js/',
            'cssUrl' => $assetsUrl.'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl.'connector.php',
        ),$config);

        $this->modx->addPackage('socialzap', $this->config['modelPath']);
    }


    // get items
    public function getItems($limit, $offset, $sortby, $sortdir, $filterUser = NULL, $filterContent = NULL, $filterSource = NULL, $cache = array())
    {

        // check if items in cache
        if (!empty($cache['cache'])) {
            $itemsCache = $this->getCache($cache['key']);
            if ($itemsCache != false) {
                return $itemsCache;
            }
        }

        // prepare query
        $where = array(
            'published' => 1,
            'deleted' => 0,
        );

        // filterUser
        if (!empty($filterUser)) {
            $where = array_merge($where, array(
                'username' => $filterUser,
            ));
        }

        // filterContent
        if (!empty($filterContent)) {
            $where = array_merge($where, array(
                'content:LIKE' => '%' . $filterContent . '%'
            ));
        }

        // filterSource
        if (!empty($filterSource)) {
            $where = array_merge($where, array(
                'source:In' => explode(',', $filterSource)
            ));
        }

        // query
        $c = $this->modx->newQuery('SocialZapItem');
        $c->where($where);
        $c->sortby($sortby, $sortdir);
        $c->limit($limit, $offset);
        $items_array = $this->modx->getCollection('SocialZapItem', $c);

        $items = array();
        $i = 1;

        if (is_array($items_array)) {
            foreach ($items_array as $item) {
                $items[] = array(
                    'id' => $item->get('id'),
                    'idx' => $i++,
                    'key' => $item->get('key'),
                    'username' => $item->get('username'),
                    'source' => $item->get('source'),
                    'type' => $item->get('media_type'),
                    'image' => $item->get('image_url'),
                    'url' => $item->get('media_url'),
                    'permalink' => $item->get('permalink'),
                    'content' => $item->get('content'),
                    'date' => $item->get('date'),
                    'properties' => json_decode($item->get('properties'), true),
                    'published' => $item->get('published'),
                    'deleted' => $item->get('deleted'),
                );
            }

            // create cache
            if (!empty($cache['cache'])) {
                $this->addCache($items, $cache['key'], $cache['time']);
            }

            return $items;
        }

        return;
    }


    // import items
    public function import($item)
    {
        // check if item already exists
        if (!$post = $this->modx->getObject('SocialZapItem', array('key' => $item->key))) {
            $post = $this->modx->newObject('SocialZapItem');
            $post->set('published', $this->published);
        }

        // save image to server
        $image = $this->copyImage($item->image_url, $item->key, $item->username, $item->source);
        if ($image === false) {
            $this->modx->log(1, 'SocialZap: Could not save image to server.');
            return false;
        }

        // save data to database
        $post->set('key', $item->key);
        $post->set('username', $item->username);
        $post->set('source', $item->source);
        $post->set('media_type', $item->media_type);
        $post->set('media_url', $item->media_url);
        $post->set('image_url', $image);
        $post->set('permalink', $item->permalink);
        $post->set('content', $this->removeEmoji($item->content));
        $post->set('date', $item->published_at);
        $post->set('properties', $item->properties);
        $post->save();

        // clear cache
        $this->modx->cacheManager->refresh(array(
            'socialzap' => '',
        ));

        return true;
    }

    // copy image to server
    private function copyImage($image, $key, $user_name, $source) {

        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $image_info   = getimagesize($image);
        $image_mime   = $image_info['mime'];

        switch ($image_mime) {
            default:
            case 'image/jpeg':
                $image_ext = 'jpg';
                break;
            case 'image/gif':
                $image_ext = 'gif';
                break;
            case 'image/png':
                $image_ext = 'png';
                break;
        }

        $image_name = $key . '.' . $image_ext;
        $image_dir_path = $this->image_path . $source . '/' . $user_name . '/';
        $image_path = $image_dir_path . $image_name;
        $image_save_dir_path = MODX_ASSETS_PATH . $image_dir_path;
        $image_save_path = $image_save_dir_path . $image_name;

        if (!file_exists($image_save_dir_path)) {
            mkdir($image_save_dir_path, 0755, true);
        }

        if (!copy($image, $image_save_path)) {
            return false;
        }

        return MODX_ASSETS_URL . $image_path;
    }


    // get items from cache
    public function getCache($key)
    {
        $options = array(
            xPDO::OPT_CACHE_KEY => 'socialzap',
        );

        return  $this->modx->cacheManager->get($key . '.socialzap');
    }

    // add items to cache
    public function addCache($items, $key, $time)
    {
        $options = array(
            xPDO::OPT_CACHE_KEY => 'socialzap',
        );

        $this->modx->cacheManager->delete($key . '.socialzap', $options);
        $this->modx->cacheManager->set($key . '.socialzap', $items, $time, $options);

    }


    // Removes all emoji from content.
    public static function removeEmoji($text)
    {
        $text = iconv('UTF-8', 'ISO-8859-15//IGNORE', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return iconv('ISO-8859-15', 'UTF-8', $text);
   }

}
