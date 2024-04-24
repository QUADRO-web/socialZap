<?php
/**
 * Import Instagram
 *
 * @package easycart
 * @subpackage processor
 */

class SocialZapImportInstagramProcessor extends modProcessor
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

        $corePath = $modx->getOption('socialzap.core_path', null, $modx->getOption('core_path') . 'components/socialzap/');
        $this->socialzap = $modx->getService('socialzap', 'SocialZap', $corePath . 'model/socialzap/', array(
            'core_path' => $corePath
        ));
    }

    public function process()
    {
        // check for correct secret
        if ($this->modx->getOption('socialzap.secret') != $_REQUEST['secret'] and !empty($this->modx->getOption('socialzap.secret'))) {
            http_response_code(401);
            return json_encode(['success' => false, 'message' => 'Missing authentication.']);
        }

        // getting the payload
        $data = json_decode(file_get_contents("php://input"));
        //$data = json_decode('{"caption":"Jetzt Anmelden zur WM 2024 - über Pfingsten in Echternacherbrück!","id":"18095954710407980","media_type":"IMAGE","media_url":"https://scontent-iad3-2.cdninstagram.com/v/t51.29350-15/437566054_725958679422771_1221964997783222342_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=18de74&_nc_ohc=O8QGUiG3tA0Ab4y0wwU&_nc_ht=scontent-iad3-2.cdninstagram.com&edm=ANo9K5cEAAAA&oh=00_AfDj9hB-We0VGMWeEnaYn5MUFqHCgZ793F0WUeOvG7v74Q&oe=662C81C4","permalink":"https://www.instagram.com/p/C5pnOY0iH0-/","timestamp":"2024-04-12T05:46:33+0000","username":"flunkybee"}');

        $sz = new SocialZap($this->modx);

        $import = $sz->import((object) array(
            'key' => $data->id,
            'username' => $data->username,
            'source' => 'instagram',
            'media_type' => $data->media_type,
            'media_url' => $data->media_url,
            'image_url' => ($data->media_type == 'VIDEO') ? $data->thumbnail_url : $data->media_url,
            'permalink' => $data->permalink,
            'content' => $data->caption,
            'date' => $data->timestamp,
            'properties' => '',
        ));

        return json_encode(array(
            'success' => $import,
        ));
    }
}

return 'SocialZapImportInstagramProcessor';
