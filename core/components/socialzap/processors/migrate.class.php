<?php
/**
 * Migrate from socialFeed
 *
 * @package socialzap
 * @subpackage processor
 */

class SocialZapMigrateProcessor extends modProcessor
{
    public $socialzap;

    /**
     * socialZap constructor.
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    public function __construct(modX $modx, array $properties)
    {
        parent::__construct($modx, $properties);

        // socialZap Service
        $corePath = $modx->getOption('socialzap.core_path', null, $modx->getOption('core_path') . 'components/socialzap/');
        $this->socialzap = $modx->getService('socialzap', 'SocialZap', $corePath . 'model/socialzap/', array(
            'core_path' => $corePath
        ));

        // socialFeed service
        $corePath = $modx->getOption('socialfeed.core_path', null, $modx->getOption('core_path') . 'components/socialfeed/');
        $this->socialzap = $modx->getService('socialfeed', 'SocialFeed', $corePath . 'model/socialfeed/', array(
            'core_path' => $corePath
        ));

    }

    public function process()
    {
        // check if user is logged in to the manager
        if (!$this->modx->user->hasSessionContext('mgr')) {
            http_response_code(401);
            return json_encode(['success' => false, 'message' => 'Missing authentication.']);
        }

        $items = $this->modx->getCollection('SocialFeedItem');

        $sz = new SocialZap($this->modx);

        foreach ($items as $data) {
            $imageUrl = str_replace('modx/modx/', 'modx/', MODX_BASE_PATH . trim($data->image_url, '/'));

            $import = $sz->import((object) array(
                'key' => $data->key,
                'username' => $data->username,
                'source' => $data->channel_type,
                'media_type' => $data->media_type,
                'media_url' => $data->media_url,
                'image_url' => $imageUrl,
                'permalink' => $data->permalink,
                'content' => $data->content,
                'date' => $data->date,
                'properties' => $data->properties,
            ));
        }

        return json_encode(array(
            'success' => $import,
        ));
    }
}

return 'SocialZapMigrateProcessor';
