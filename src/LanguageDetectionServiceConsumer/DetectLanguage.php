<?php
/**
 * language-detection-service Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\LanguageDetectionServiceConsumer;

class DetectLanguage
{
    /**
     * @var string
     */
    protected $apiUrl = 'http://language-detection-serv-16d136bb-1.ac1f2917.cont.dockerapp.io:32783';

    /**
     * DetectLanguage constructor.
     *
     * @param $apiUrl
     */
    public function __construct($apiUrl = null)
    {
        if ($apiUrl) {
            $this->setApiUrl($apiUrl);
        }
    }

    /**
     * @param $textToDetect
     *
     * @param bool $normalizeText
     *
     * @return null|\Sta\LanguageDetectionServiceConsumer\DetectionResult
     */
    public function detect($textToDetect, $normalizeText = true)
    {
        $curl            = $this->_getCurl($textToDetect, true, $normalizeText);
        $detectionResult = $this->_dispatchCurlRequest($curl);

        return $detectionResult ? $detectionResult[0] : null;
    }

    /**
     * @param $textToDetect
     *
     * @param bool $normalizeText
     *
     * @return \Sta\LanguageDetectionServiceConsumer\DetectionResult[]
     */
    public function detectAllLanguages($textToDetect, $normalizeText = true)
    {
        $curl            = $this->_getCurl($textToDetect, false, $normalizeText);
        $detectionResult = $this->_dispatchCurlRequest($curl);

        return $detectionResult;
    }

    /**
     * Alias for {@link \Sta\LanguageDetectionServiceConsumer\DetectLanguage::detectAllLanguages()}
     *
     * @param $textToDetect
     *
     * @param bool $normalizeText
     *
     * @return \Sta\LanguageDetectionServiceConsumer\DetectionResult[]
     */
    public function detectAll($textToDetect, $normalizeText = true)
    {
        return $this->detectAllLanguages($textToDetect, $normalizeText);
    }

    /**
     * @param $endPoint
     *
     * @return string
     */
    public function getApiUrl($endPoint)
    {
        return trim($this->apiUrl, '/') . '/' . ltrim($endPoint, '/');
    }

    /**
     * @param string $apiUrl
     *
     * @return $this
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @param $curl
     *
     * @return \Sta\LanguageDetectionServiceConsumer\DetectionResult[]
     */
    private function _dispatchCurlRequest($curl)
    {
        $response = curl_exec($curl);

        /* Check for errors. */
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            $response = [];
        }

        curl_close($curl);

        if ($response) {
            $response = json_decode($response);
        }

        $detectionResult = [];
        if ($response instanceof \stdClass) {
            // if it is an stdClass, its because the client used the 'most-probably' parameter,
            // witch returns only one language
            $detectionResult[] = $this->convertApiDetectResponseItem($response);
        } else if (is_array($response)) {
            foreach ($response as $responseItem) {
                $detectionResult[] = $this->convertApiDetectResponseItem($responseItem);
            }
        }

        return $detectionResult;
    }

    /**
     * @param $textToDetect
     * @param $onlyTheMostProbably
     * @param $normalizeText
     *
     * @return resource
     */
    private function _getCurl($textToDetect, $onlyTheMostProbably, $normalizeText)
    {
        $endPoint = '/detect';
        $query    = [];

        if ($onlyTheMostProbably) {
            $query[] = 'most-probably';
        }

        if (!$normalizeText) {
            $query[] = 'do-not-normalize-text';
        }

        if ($query) {
            $query = implode('&', $query);
            $endPoint .= '?' . $query;
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 7,
            CURLOPT_URL => $this->getApiUrl($endPoint),
            CURLOPT_MAXREDIRS => 15,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                't' => $textToDetect,
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        return $ch;
    }

    /**
     * @param \stdClass $responseItem
     *
     * @return \Sta\LanguageDetectionServiceConsumer\DetectionResult
     */
    private function convertApiDetectResponseItem(\stdClass $responseItem
    ): \Sta\LanguageDetectionServiceConsumer\DetectionResult {
        $item = new DetectionResult();
        $item->setConfidence(isset($responseItem->confidence) ? $responseItem->confidence : false);
        $item->setProbability(isset($responseItem->probability) ? $responseItem->probability : 0);
        $item->setLanguageName(isset($responseItem->name) ? $responseItem->name : null);
        $item->setLanguageCode(isset($responseItem->code) ? $responseItem->code : null);

        return $item;
    }

}
