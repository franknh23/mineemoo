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



class Mirasvit_Rma_Model_FieldTest extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @loadFixture data
     */
    public function exampleTest()
    {
        $this->assertEquals(1, 1);
    }

    /**
     * @test
     * @loadFixture data
     * @dataProvider exampleProvider
     *
     * @param int $expected
     * @param int $input
     */
    public function example2Test($expected, $input)
    {
        $result = $input;
        $this->assertEquals($expected, $result);
    }

    public function exampleProvider()
    {
        return array(
            array(1, 1),
        );
    }
}
