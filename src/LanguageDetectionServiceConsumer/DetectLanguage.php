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
    protected $apiUrl = 'http://language-detection-serv-16d136bb-1.2d47c013.cont.dockerapp.io:32777';

    /**
     * DetectLanguage constructor.
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
     * @return null|\Sta\LanguageDetectionServiceConsumer\DetectionResult
     */
    public function detect($textToDetect)
    {
        $curl            = $this->_getCurl($textToDetect);
        $detectionResult = $this->_dispatchCurlRequest($curl);

        return $detectionResult;
    }

    /**
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
     * @return null|\Sta\LanguageDetectionServiceConsumer\DetectionResult
     */
    private function _dispatchCurlRequest($curl)
    {
        $response = curl_exec($curl);

        /* Check for errors. */
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            $response = null;
        }

        curl_close($curl);

        $detectionResult = null;
        if ($response) {
            $detectionResult = new DetectionResult();
            $response        = json_decode($response);
            $detectionResult->setConfidence(isset($response->confidence) ? $response->confidence : false);
            $detectionResult->setProbability(isset($response->probability) ? $response->probability : 0);
            $detectionResult->setLanguageName(isset($response->languageName) ? $response->languageName : null);
            $detectionResult->setLanguageCode(isset($response->languageCode) ? $response->languageCode : null);
        }

        return $detectionResult;
    }

    /**
     * @param $textToDetect
     *
     * @return resource
     */
    private function _getCurl($textToDetect)
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 7,
            CURLOPT_URL => $this->getApiUrl('/detect'),
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


}
