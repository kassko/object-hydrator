<?php

namespace Kassko\ObjectHydrator;

class ConfigFlattener
{
    public function packSettings(array $settings, array &$packedSettings)
    {
        foreach ($settings as $key => $setting) {
            if (is_array($setting)) {
                $path = $key;
                $packedSetting = [];
                $this->packSettings($setting, $packedSetting);
            }
        }
    }

    public function getUnpackedSettings(array $settings)
    {
        $unpackedSettings = [];
        $this->unpackSettings($settings, $unpackedSettings);

        return $unpackedSettings;
    }

    public function unpackSettings(array $settings, array &$unpackedSettings)
    {
        foreach ($settings as $namespace => $setting) {

            if (is_array($setting)) {
                $unpackedSetting = [];
                $this->unpackSettings($setting, $unpackedSetting);
            } else {
                $unpackedSetting = $setting;
            }

            if (null === strstr($namespace, '.')) {

                $nsToUnpack[$namespace] = false;
            }  else {

                $namespaceParts = explode('.', $namespace);

                list(, $namespacePart) = each($namespaceParts);
                $unpackedSettings[$namespacePart] = null;
                $unpackedSettingsRef = & $unpackedSettings[$namespacePart];

                //Since there is a '.', we always loop at least one time.
                while (list(, $namespacePart) = each($namespaceParts)) {

                    $unpackedSettingsRef[$namespacePart] = null;
                    $unpackedSettingsRef = & $unpackedSettingsRef[$namespacePart];
                }
                $unpackedSettingsRef = $unpackedSetting;
            }
        }
    }
}
