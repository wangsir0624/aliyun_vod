<?php
namespace Wangjian\Alivod;

class AliyunVod {
    /**
     * API host
     * @const string
     */
    const API_HOST = 'http://vod.cn-shanghai.aliyuncs.com';

    /**
     * the video status constants
     * @const string
     */
    const STATUS_UPLOADING = 'Uploading';
    const STATUS_UPLOADFAIL = 'UploadFail';
    const STATUS_UPLOADSUCC = 'UploadSucc';
    const STATUS_TRANSCODING = 'Transcoding';
    const STATUS_TRANSCODEFAIL = 'TranscodeFail';
    const STATUS_BLOCKED = 'Blocked';
    const STATUS_NORMAL = 'Normal';

    /**
     * the sort by constant
     * @const string
     */
    const SORTBY_CREATIONTIME_ASC = 'CreationTime:Asc';
    const SORTBY_CREATIONTIME_DESC = 'CreationTime:Desc';

    /**
     * the image type constant
     * @const string
     */
    const IMGTYPE_COVER = 'cover';
    const IMGTYPE_WATERMARK = 'watermark';

    /**
     * the api return type, xml or json
     * @var string
     */
    protected $format = 'json';

    /**
     * api version
     * @var string
     */
    protected $version = '2017-03-21';

    /**
     * signature algorithm
     * @var string
     */
    protected $signatureMethod = 'HMAC-SHA1';

    /**
     * signature algorithm version
     * @var string
     */
    protected $signatureVersion = '1.0';

    /**
     * Access Key ID
     * @var string
     */
    protected $accessKeyId;

    /**
     * Access Key Secret
     * @var string
     */
    protected $accessKeySecret;

    /**
     * AliyunVod constructor.
     * @param string $accessKeyId
     * @param string $accessKeySecret
     * @param array $configs
     */
    public function __construct($accessKeyId, $accessKeySecret, $configs = []) {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;

        $this->format = isset($configs['format']) ? $configs['format'] : $this->format;
        $this->version = isset($configs['version']) ? $configs['version'] : $this->version;
        $this->signatureMethod = isset($configs['signatureMethod']) ? $configs['signatureMethod'] : $this->signatureMethod;
        $this->signatureVersion = isset($configs['signatureVersion']) ? $configs['signatureVersion'] : $this->signatureVersion;
    }

    /**
     * create video upload auth
     * @param string $title  the video title
     * @param string $filename  the video filename
     * @param string $filesize  the video file size
     * @param string $description  the video description
     * @param string $coverUrl  the video cover url
     * @param int $cateId  the video category id
     * @param array $tags  the video tags
     * @return array
     */
    public function createUploadVideo($title, $filename, $filesize, $description, $coverUrl = null, $cateId = null, $tags = null) {
        $request = $this->createRequest([
            'Action' => 'CreateUploadVideo',
            'Title' => $title,
            'FileName' => $filename,
            'FileSize' => $filesize,
            'Description' => $description
        ]);

        if(!is_null($coverUrl)) {
            $request->CoverURL = $coverUrl;
        }

        if(!is_null($cateId)) {
            $request->CateId = $cateId;
        }

        if(!is_null($tags)) {
            $request->Tags = implode(',', $tags);
        }

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return $result;
        }
    }

    /**
     * refresh the video upload auth
     * @param string $videoId  the video id
     * @return array
     */
    public function refreshUploadVideo($videoId) {
        $request = $this->createRequest([
           'Action' => 'RefreshUploadVideo',
            'VideoId' => $videoId
        ]);

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return $result;
        }
    }

    /**
     * create the image upload auth
     * @param string $imgType  the image type, cover or watermark
     * @param string $imgExt
     * @return array
     */
    public function createUploadImage($imgType, $imgExt = 'png') {
        $request = $this->createRequest([
           'Action' => 'CreateUploadImage',
            'ImageType' => $imgType,
            'ImageExt' => $imgExt
        ]);

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return $result;
        }
    }

    /**
     * get the video information
     * @param string $videoId  the video id
     * @return array
     */
    public function getVideoInfo($videoId) {
        $request = $this->createRequest([
           'Action' => 'GetVideoInfo',
            'VideoId' => $videoId
        ]);

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return $result['Video'];
        }
    }

    /**
     * update the video information
     * @param string $videoId
     * @param string $title
     * @param string $description
     * @param string $coverUrl
     * @param string $cateId
     * @param string $tags
     * @return array|bool
     */
    public function updateVideoInfo($videoId, $title = null, $description = null, $coverUrl = null, $cateId = null, $tags = null) {
        $request = $this->createRequest([
           'Action' => 'UpdateVideoInfo',
            'VideoId' => $videoId,
        ]);

        if(!is_null($title)) {
            $request->Title = $title;
        }

        if(!is_null($description)) {
            $request->Description = $description;
        }

        if(!is_null($coverUrl)) {
            $request->CoverUrl = $coverUrl;
        }

        if(!is_null($cateId)) {
            $request->CateId = $cateId;
        }

        if(!is_null($tags)) {
            $request->Tags = implode(',', $tags);
        }

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return true;
        }
    }

    /**
     * delete videos
     * @param array|string $videoIds
     * @return array
     */
    public function deleteVideo($videoIds) {
        $videoIds = is_string($videoIds) ? (array)$videoIds : $videoIds;

        $request = $this->createRequest([
            'Action' => 'DeleteVideo',
            'VideoIds' => implode(',', $videoIds)
        ]);

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return true;
        }
    }

    /**
     * get the video list
     * @param string $status  the video status
     * @param int $cateId  the category id
     * @param int $pageNo  the page number
     * @param int $pageSize  the page size
     * @param string $sortBy
     * @return array
     */
    public function getVideoList($status = null, $cateId = null, $pageNo = null, $pageSize = null, $sortBy = null) {
        $request = $this->createRequest([
           'Action' => 'GetVideoList'
        ]);

        if(!is_null($status)) {
            $request->Status = $this->status[$status];
        }

        if(!is_null($cateId)) {
            $request->CateId = $cateId;
        }

        if(!is_null($pageNo)) {
            $request->PageNo = $pageNo;
        }

        if(!is_null($pageSize)) {
            $request->PageSize = $pageSize;
        }

        if(!is_null($sortBy)) {
            $request->SortBy = $this->sortBy[$sortBy];
        }

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return $result['VideoList']['Video'];
        }
    }

    /**
     * get the video play auth
     * @param int $videoId
     * @return array
     */
    public function getVideoPlayAuth($videoId) {
        $request = $this->createRequest([
           'Action' => 'GetVideoPlayAuth',
            'VideoId' => $videoId
        ]);

        $result = $this->sendRequest($request);
        $result = json_decode($result, true);

        if(isset($result['Code'])) {
            return ['errCode' => $result['Code'], 'errMsg' => $result['Message']];
        } else {
            return $result;
        }
    }

    /**
     * create a request
     * @param array $parameters
     * @param string $method
     * @return AliyunVodRequest
     */
    protected function createRequest($parameters = [], $method = 'GET') {
        //common parameters
        $commonParameters = [
            'Format' => $this->format,
            'Version' => $this->version,
            'AccessKeyId' => $this->accessKeyId,
            'SignatureMethod' => $this->signatureMethod,
            'SignatureVersion' => $this->signatureVersion
        ];

        //timestamp parameter
        $timezone = new \DateTimeZone('UTC');
        $time = new \DateTime('now', $timezone);
        $commonParameters['Timestamp'] = $time->format('Y-m-d') . 'T' . $time->format('H:i:s') . 'Z';

        //nonce parameter
        $commonParameters['SignatureNonce'] = $this->getNonce();

        $parameters = array_merge($commonParameters, $parameters);

        return new AliyunVodRequest($parameters, $method);
    }

    /**
     * create request signature
     * @param AliyunVodRequest $request
     * @throws \Exception
     */
    protected function signRequest(AliyunVodRequest $request) {
        $signature = '';

        //sort the parameters by dictionary ordering
        $parameters = $request->parameters();
        ksort($parameters);

        //urlencode the parameter key and parameter value
        foreach($parameters as $key => $value) {
            $signature .= urlencode($key).'='.urlencode($value).'&';
        }
        $signature = rtrim($signature, '&');

        //generate the canonicalized query string
        $signature = $request->method() . '&' . urlencode('/') . '&' . urlencode($signature);

        //sign the canonicalized query string with signature method
        switch($this->signatureMethod) {
            case 'HMAC-SHA1':
                $signature = hash_hmac('sha1', $signature, $this->accessKeySecret . '&', true);
                break;
            default:
                throw new \Exception('Unsupported Signature Method');
                break;
        }

        //base64 encode
        $signature = base64_encode($signature);

        //add the signature pameter to the request
        $request->Signature = $signature;
    }

    /**
     * send the request
     * @param AliyunVodRequest $request
     * @param bool $signature  whether sign the request before sending
     * @return string
     */
    protected function sendRequest(AliyunVodRequest $request, $signature = true) {
        if($signature) {
            $this->signRequest($request);
        }

        switch($request->method()) {
            case 'GET':
                $ch = curl_init(self::API_HOST . '?' . $request->serialize());
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                curl_close($ch);
                break;
        }

        return $result;
    }

    /**
     * get a random string
     * @return string
     */
    protected function getNonce() {
        //获取当前时间戳，毫秒为单位
        $microtime = microtime(true);

        //获取客户端IP
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');

        //获取一个32位的随机数
        $len = 32;
        $tokens =  [
            '0','1','2','3','4','5','6','7','8','9',
            'a','b','c','d','e','f','g','h','i','j',
            'k','l','m','n','o','p','q','r','s','t',
            'u','v','w','x','y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
            'W', 'X', 'Y', 'Z'
        ];
        $tokenCount = count($tokens);
        for($i = 0, $randStr = ''; $i < $len; $i++) {
            $randStr .= $tokens[rand(0, $tokenCount-1)];
        }

        return (10000*$microtime) . str_replace('.', '', $ip) . $randStr;
    }
}