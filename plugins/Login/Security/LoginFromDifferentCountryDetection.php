<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Security;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\Login\Emails\LoginFromDifferentCountryEmail;
use Piwik\Plugins\Login\Emails\SuspiciousLoginAttemptsInLastHourEmail;
use Piwik\Plugins\Login\Model;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\UserCountry\LocationProvider;

class LoginFromDifferentCountryDetection
{
    /**
     * @var Model
     */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function isEnabled()
    {
        // we need at least one GeoIP provider that is not the default or disabled one
        $geoIPWorking = $this->isGeoIPWorking();



        return true;
    }


    private function isGeoIPWorking(): bool
    {
        $provider = LocationProvider::getCurrentProvider();

        return $provider instanceof GeoIp2
            && $provider->isAvailable()
            && $provider->isWorking();
    }

    private function getCurrentLoginCountry(): string
    {
        $allProviderInfo = LocationProvider::getAllProviderInfo($newline = ' ', $includeExtra = true);
    }

    public function check(string $login): void
    {
        $isDifferentCountries = $this->model->isCountryDifferentToLastLoginCountry($login);

        echo '<pre>';
        $allProviderInfo = LocationProvider::getAllProviderInfo($newline = ' ', $includeExtra = true);

        var_dump($allProviderInfo);

        exit;
    }


    private function sendLoginFromDifferentCountryEmailToUser($login, $country, $ip, $dateTime)
    {
        try {
            // create from DI container so plugins can modify email contents if they want
            $email = StaticContainer::getContainer()->make(LoginFromDifferentCountryEmail::class, [
                'login' => $login,
                'country' => $country,
                'ip' => $ip,
                'dateTime' => $dateTime,
            ]);
            $email->send();
        } catch (\Exception $ex) {
            // log if error is not that we can't find a user
            if (strpos($ex->getMessage(), 'unable to find user to send') === false) {
                StaticContainer::get(LoggerInterface::class)->info(
                    'Error when sending ' . SuspiciousLoginAttemptsInLastHourEmail::class . ' email. User exists but encountered {exception}',
                    ['exception' => $ex]
                );
            }
        }
    }
}
