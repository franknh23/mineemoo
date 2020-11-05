<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.02.12
 * Time: 16:07
 * To change this template use File | Settings | File Templates.
 */
class Stork_Shipcloud_Helper_Help extends Mage_Core_Helper_Abstract
{
    static public function escapeXML($string){
        $string = preg_replace('/&/is','&amp;',$string);
        $string = preg_replace('/</is','&lt;',$string);
        $string = preg_replace('/>/is','&gt;',$string);
        $string = preg_replace('/\'/is','&apos;',$string);
        $string = preg_replace('/"/is','&quot;',$string);
        $string = str_replace(array('ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ü'), array('a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z', 'u'),$string);
        return mb_encode_numericentity(trim($string), array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
    }
}
