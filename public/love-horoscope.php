<?php

declare(strict_types=1);
use Prokerala\Api\Horoscope\Service\DailyLovePrediction;
use Prokerala\Common\Api\Exception\AuthenticationException;
use Prokerala\Common\Api\Exception\Exception;
use Prokerala\Common\Api\Exception\QuotaExceededException;
use Prokerala\Common\Api\Exception\RateLimitExceededException;
use Prokerala\Common\Api\Exception\ValidationException;

require __DIR__ . '/bootstrap.php';


$sample_name = 'love-horoscope';
$calculators = [];

$arSign = ['aries', 'taurus', 'gemini', 'cancer', 'leo', 'virgo', 'libra', 'scorpio', 'sagittarius', 'capricorn', 'aquarius', 'pisces'];
$signSymbols = ['aries' => '♈', 'taurus' => '♊', 'gemini' => '♌', 'cancer' => '♎', 'leo' => '♐', 'virgo' => '♒', 'libra' => '♉', 'scorpio' => '♋', 'sagittarius' => '♍', 'capricorn' => '♏', 'aquarius' => '♑', 'pisces' => '♓'];
foreach ($arSign as $value) {
    $arSignData[$value] = $signSymbols[$value] . '  ' . ucfirst($value);
}


$datetime = new DateTimeImmutable('now');

$submit = $_POST['submit'] ?? 0;
$sign_one = null;
$sign_two = null;

if ($submit) {
    $sign_one = $_POST['sign_one'];
    $sign_two = $_POST['sign_two'];
}

$result = [];
$errors = [];

if ($submit) {
    try {
        $method = new DailyLovePrediction($client);
        $method->setTimeZone($datetime->getTimezone());
        $result = $method->process($datetime, $sign_one, $sign_two);
        $dailyLovePredictions = $result->getDailyLovePredictions();
    } catch (ValidationException $e) {
        $errors = $e->getValidationErrors();
    } catch (QuotaExceededException $e) {
        $errorsQuota = ['message' => '<p class="">The demo is currently disabled. You may request access to the demo by contacting our support team.</p><p class="">Please note that the demo page <span class="b">does not use credits from your account.</span> You can also download the full source code of this demo from the following link: <a href="https://github.com/prokerala/astrology-api-demo">https://github.com/prokerala/astrology-api-demo</a></p><div class=""><a href="https://api.prokerala.com/account/contact" class="btn btn-sm btn-info b">Request Demo</a></div>'];
    } catch (RateLimitExceededException $e) {
        $errors['message'] = 'ERROR: Rate limit exceeded. Throttle your requests.';
    } catch (AuthenticationException $e) {
        $errors = ['message' => $e->getMessage()];
    } catch (Exception $e) {
        $errors = ['message' => "API Request Failed with error {$e->getMessage()}"];
    }
}

$apiCreditUsed = $client->getCreditUsed();

include DEMO_BASE_DIR . '/templates/love-horoscope.tpl.php';
