<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Emails;

use Piwik\Common;
use Piwik\Date;
use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\Login\PasswordResetter;
use Piwik\Plugins\UsersManager\Model as UsersManagerModel;
use Piwik\Url;
use Piwik\View;

class LoginFromDifferentCountryEmail extends Mail
{
    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var array
     */
    private $user;


    public function __construct($login, $country, $ip)
    {
        parent::__construct();

        $this->login = $login;
        $this->country = $country;
        $this->ip = $ip;

        $model = new UsersManagerModel();
        $this->user = $model->getUser($this->login);

        $this->setUpEmail();
    }

    private function setUpEmail()
    {
        if (
            empty($this->user)
            || empty($this->user['login'])
        ) {
            throw new \Exception('Unexpected error: unable to find user to send ' . __CLASS__);
        }

        $userEmailAddress = $this->user['email'];

        $this->setDefaultFromPiwik();
        $this->addTo($userEmailAddress);
        $this->setSubject($this->getDefaultSubject());
        $this->addReplyTo($this->getFrom(), $this->getFromName());
        $this->setWrappedHtmlBody($this->getDefaultBodyView());
    }

    private function getDefaultSubject()
    {
        return Piwik::translate('Login_LoginFromDifferentCountryEmailSubject');
    }

    private function getDateAndTimeFormatted(): string
    {
        $date = Date::factory('now', 'UTC');
        return $date->toString('Y-m-d H:i:s');
    }

    private function getPasswordResetLink(): string
    {
        if (!empty($this->user)) {
            $passwordResetter = new PasswordResetter();
            $keySuffix = time() . Common::getRandomString($length = 32);

            // Seems like we need to save the info, however there's no new password yet
            // $this->savePasswordResetInfo($login, $newPassword, $keySuffix);
            // Can we even link to the reset password view?

            $resetToken = $passwordResetter->generatePasswordResetToken($this->user, $keySuffix);

            // Create the reset URL
            return Url::getCurrentUrlWithoutQueryString()
                . '?module=Login&action=resetPassword&login=' . urlencode($this->login)
                . '&resetToken=' . urlencode($resetToken);
        }

        return '';
    }

    private function getEnable2FALink(): string
    {
        $siteId = isset($this->user['defaultReport']) ? (int) $this->user['defaultReport'] : 1;

        return Url::getCurrentUrlWithoutQueryString()
            . '?module=TwoFactorAuth&action=setupTwoFactorAuth'
            . '&idSite=' . $siteId;
    }

    private function getDefaultBodyView()
    {
        $view = new View('@Login/_loginFromDifferentCountryEmail.twig');
        $view->login = $this->login;
        $view->country = $this->country;
        $view->ip = $this->ip;
        $view->dateTime = $this->getDateAndTimeFormatted();
        $view->resetPasswordLink = $this->getPasswordResetLink();
        $view->enable2FALink = $this->getEnable2FALink();

        return $view->render();
    }
}
