<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

class UserSettings extends \Piwik\Settings\Plugin\UserSettings
{
    /** @var Setting */
    public $enableLoginCountryChangeNotification;

    protected function init()
    {
        $this->enableLoginCountryChangeNotification = $this->createEnableLoginCountryChangeNotification();
    }

    private function createEnableLoginCountryChangeNotification()
    {
        return $this->makeSetting('enableLoginCountryChangeNotification',
            $default = true,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('Login_SettingCountryChangeNotificationEnable');
                $field->description = Piwik::translate('Login_SettingCountryChangeNotificationEnableHelp');
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;

                $field->inlineHelp .= '<br>' . Piwik::translate('Login_SettingCountryChangeNotificationEnableHelpGeoIPRequired') . '</strong>';
            }
        );
    }

}
