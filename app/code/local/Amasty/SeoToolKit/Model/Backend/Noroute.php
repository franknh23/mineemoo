<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Backend_Noroute extends Mage_Core_Model_Config_Data
{
    const CMS_NOROUTE = 'cms/index/noRoute';
    const TOOLKIT_NOROUTE = 'amseotoolkit/noroute/index';

    /**
     * Change no_route setting if need.
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        if ($value == 0) {
            $noRouteAction = self::CMS_NOROUTE;
        } else {
            $noRouteAction = self::TOOLKIT_NOROUTE;
        }
        if ($this->getScope()) {
            Mage::getConfig()->saveConfig(
                'web/default/no_route', $noRouteAction, $this->getScope(), $this->getScopeId()
            );
        }
        parent::setValue($value);

        return $this;
    }
}
