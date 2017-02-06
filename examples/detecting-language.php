<?php

require_once __DIR__ . '/../vendor/autoload.php';

$text = $argc > 1 ? $argv[1] : 'Bonjour le monde.';

$detectLanguage   = new \Sta\LanguageDetectionServiceConsumer\DetectLanguage();
$detectionResults = $detectLanguage->detectAllLanguages($text);

var_dump(
    [
        'text' => $text,
        'result' => $detectionResults,
        'tip' => 'You can test detection using others texts by passing the text as the first parameter to this script.',
    ]
);
