<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Emails;

use Piwik\Mail;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model as UserManagerModel;
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
     * @var string
     */
    private $dateTime;



    public function __construct($login, $country, $ip, $dateTime)
    {
        parent::__construct();

        $this->login = $login;
        $this->country = $country;
        $this->ip = $ip;
        $this->dateTime = $dateTime;

        $this->setUpEmail();
    }

    private function setUpEmail()
    {
        $model = new UserManagerModel();
        $user = $model->getUser($this->login);
        if (
            empty($user)
            || empty($user['login'])
        ) {
            throw new \Exception('Unexpected error: unable to find user to send ' . __CLASS__);
        }

        $userEmailAddress = $user['email'];

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

    private function getDefaultBodyView()
    {
        $view = new View('@Login/_loginFromDifferentCountryEmail.twig');
        $view->login = $this->login;
        $view->country = $this->country;
        $view->ip = $this->ip;
        $view->dateTime = $this->dateTime;
        $view->resetPasswordLink = '';
        $view->enable2FALink = '';


        return $view->render();
    }
}
