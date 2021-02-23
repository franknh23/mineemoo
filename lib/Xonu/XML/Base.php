<?php
/**
 * Base class for SEPA files
 *
 * @license GNU Lesser General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Lesser Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Damien Overeem (www.sohosted.com), Pawel Kazakow (www.xonu.de)
 * @package     Sepa PHP XML tools
 * @version     1.0
 *
 */
class Sepa_Base {

    /**
     * The generated XML file
     * @var SimpleXml
     */
    protected $_xml;

    /**
     * Return the XML string.
     * @return string
     */
    public function asXML() {
        $this->_generateXml();
        return $this->_xml->asXML();
    }

    /**
     * Output the XML string to the screen.
     */
    public function outputXML() {
        $this->_generateXml();
        header('Content-type: text/xml');
        echo $this->_xml->asXML();
    }

    /**
     * Download the XML string into XML File
     */
    public function downloadXML() {
        $this->_generateXml();
        header("Content-type: text/xml");
        header('Content-disposition: attachment; filename=sepa_' . date('dmY-His') . '.xml');
        echo $this->_xml->asXML();
        exit();
    }

    /**
     * Format an integer as a monetary value.
     */
    public static function intToCurrency($amount) {
        return sprintf("%01.2f", ($amount / 100));
    }

    /**
     * Format an float as a monetary value.
     */
    public static function floatToCurrency($amount) {
        return number_format($amount, 2, '.', '');
    }

    /**
     * @param type $code
     * @return string currency ISO code
     * @throws Exception
     */
    public static function validateCurrency($code) {
        if (strlen($code) !== 3) throw new Exception("Invalid ISO currency code: $code");
        return $code;
    }

    /**
     * Removes all non accepted characters
     *
     * @param string $string
     * @param int $length
     * @return type
     */
    public static function alphanumeric($input, $length) {
        // valid characters
        // a b c d e f g h i j k l m n o p q r s t u v w x y z
        // A B C D E F G H I J K L M N O P Q R S T U V W X Y Z
        // 0 1 2 3 4 5 6 7 8 9
        // / - ? : ( ) . , ‘ +
        // Space
        //
        // normalize string to contain only valid characters
        $normalizeChars = array(
            'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'Ae', 'Æ'=>'AE', 'Ç'=>'C',
            'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth',
            'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
            'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'Ue', 'Ý'=>'Y',

            'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'ae', 'æ'=>'ae', 'ç'=>'c',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth',
            'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'oe', 'ø'=>'o',
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'ue', 'ý'=>'y',

            'ß'=>'ss', 'þ'=>'thorn', 'ÿ'=>'y',

            '&'=>'u.', '@'=>'at', '#'=>'h', '$'=>'s', '%'=>'perc', '^'=>'-','*'=>'-'
        );

        $output = strtr($input, $normalizeChars);
        $output = substr($output, 0, $length);

        return $output;
    }

    /**
     * Alternative to the asXml method
     */
    public function __toString() {
        return $this->asXml();
    }


}