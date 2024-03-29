<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   2.1.0-beta
 * @build     1359
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



/**
 * @method Mirasvit_Rma_Block_Adminhtml_Report_Rma_Grid getGrid()
 */
class Mirasvit_Rma_Block_Adminhtml_Report_Rma_Chart extends Mage_Core_Block_Template
{
    public function getCollection()
    {
        return $this->getGrid()->getCollection();
    }

    public function isShowChart()
    {
        $collection = $this->getCollection();
        if ($this->getCollection()->count() > 1 && $this->getFilterData()->getReportType() == 'all') {
            return true;
        }

        return false;
    }

    /************************/
}
