<?php
/**
 * Transaction part of the DirectDebit XML structure
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
class Sepa_DirectDebit_Transaction {

    /**
     * End to End ID ( Tag <EndToEndId> )
     * @var string
     */
    private $_endToEndId;


    /**
     * Sequence Type
     * FRST - first debit payment using recurring mandate
     * RCUR - debit payment using recurring mandate
     * OOFF - one-time debit payment
     * FNAL - last debit payment using recurring mandate
     */
    private $_sequenceType;
    private $_validSequenceTypes = array(
        Sepa_DirectDebit::SeqTp_FirstRecurrent,
        Sepa_DirectDebit::SeqTp_Recurrent,
        Sepa_DirectDebit::SeqTp_OneTime,
        Sepa_DirectDebit::SeqTp_LastRecurrent
    );
        /**
     * Transaction amount ( Tag <InstdAmt> )
     * @var string
     */
    private $_amount;

    /**
     * Transaction identifier ( Tag <MndtId> )
     * @var string
     */
    private $_transactionIdentifier;

    /**
     * signatureDate ( Tag <DtofSgntr> )
     * @var string
     */
    private $_signatureDate;

    /**
     * Debtor's name ( Tag <Dbtr> -> <Nm> )
     * @var string
     */
    private $_debtorName;

    /**
     * Debtor's name ( Tag <DbtrAcct> -> <Id> -> <IBAN> )
     * @var string
     */
    private $_debtorIban;

    /**
     * Debtor's BIC code ( Tag <DbtrAgt> -> <FinInstnId> -> <BIC> )
     * @var string
     */
    private $_debtorBic;

    /**
     * Transaction description ( Tag <RmInf> -> <Ustrd> )
     * @var string
     */
    private $_transactionDescription;

    /**
     * Factory for easily using the class as parameter for addTransaction
     */
    public static function factory() {
        return new Sepa_DirectDebit_Transaction();
    }

    /**
     * Gets the End to End ID
     *
     * @return string
     */
    public function getEndToEndId() {
        return $this->_endToEndId;
    }

    /**
     * Sets the End to End ID
     *
     * @param   string $endToEndId
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setEndToEndId($endToEndId) {
        $this->_endToEndId = $endToEndId;
        return $this;
    }


    /**
     * Sequence Type
     *
     * @param $SeqTp
     * @return Sepa_DirectDebit_Transaction
     */
    public function setSequenceType($SeqTp) {
        if($this->validateSequenceType($SeqTp)) $this->_sequenceType = $SeqTp;
        return $this;
    }

    public function validateSequenceType($SeqTp) {
        return in_array($SeqTp, $this->_validSequenceTypes);
    }


    /**
     * Gets the transactoun amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->_amount;
    }

    /**
     * Sets the End to End ID
     *
     * @param   string $endToEndId
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setAmount($amount) {
        $this->_amount = Sepa_Base::floatToCurrency($amount);
        return $this;
    }

    /**
     * Gets the transaction identifier
     *
     * @return string
     */
    public function getTransactionIdentifier() {
        return $this->_transactionIdentifier;
    }

    /**
     * Sets the End to End ID
     *
     * @param   string $endToEndId
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setTransactionIdentifier($transactionIdentifier) {
        $this->_transactionIdentifier = $transactionIdentifier;
        return $this;
    }

    /**
     * Gets the signature date
     *
     * @return string
     */
    public function getSignatureDate() {
        return $this->_signatureDate;
    }

    /**
     * Sets the End to End ID
     *
     * @param   string $endToEndId
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setSignatureDate($signatureDate) {
        $this->_signatureDate = $signatureDate;
        return $this;
    }

    /**
     * Gets the debtor's name
     *
     * @return string
     */
    public function getDebtorName() {
        return $this->_debtorName;
    }

    /**
     * Sets the debtor's name
     *
     * @param   string $debtorName
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setDebtorName($debtorName) {
        $this->_debtorName = Sepa_Base::alphanumeric($debtorName, 70);
        return $this;
    }

    /**
     * Gets the debtors iban
     *
     * @return string
     */
    public function getDebtorIban() {
        return $this->_debtorIban;
    }

    /**
     * Sets the debtors iban
     *
     * @param   string $debtorIban
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setDebtorIban($debtorIban) {
        $debtorIban = str_replace(' ', '', $debtorIban);
        $this->_debtorIban = $debtorIban;
        return $this;
    }

    /**
     * Gets the debtor's BIC
     *
     * @return string
     */
    public function getDebtorBic() {
        return $this->_debtorBic;
    }

    /**
     * Sets the debtor's BIC
     *
     * @param   string $debtorBic
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setDebtorBic($debtorBic) {
        $this->_debtorBic = $debtorBic;
        return $this;
    }

    /**
     * Gets the transaction description
     *
     * @return string
     */
    public function getTransactionDescription() {
        return $this->_transactionDescription;
    }

    /**
     * Sets the transaction description
     *
     * @param   string $transactionDescription
     * @return  Sepa_DirectDebit_Transaction
     */
    public function setTransactionDescription($transactionDescription) {
        $this->_transactionDescription = Sepa_Base::alphanumeric($transactionDescription, 140);
        return $this;
    }

    /**
     * Returns the simpleXml version of the xml for this transaction
     *
     * @param SimpleXmlElement $SimpleXmlElement
     * @return SimpleXmlElement
     */
    public function appendToXml($SimpleXmlElement) {

        $Xml = $SimpleXmlElement->addChild('DrctDbtTxInf'); /* ISO: 2.28 */
        $Xml->addChild('PmtId')->addChild('EndToEndId', $this->getEndToEndId()); /* ISO: 2.29, 2.31 */

        if(isset($this->_sequenceType)) {
            $Xml->addChild('PmtTpInf')->addChild('SvcLvl')->addChild('Cd', 'SEPA'); /* ISO: 2.9 */
            $Xml->PmtTpInf->addChild('SeqTp', $this->_sequenceType); /* ISO: 2.14 */
        }

        $Xml->addChild('InstdAmt', $this->getAmount()); /* ISO: 2.44 */
        $Xml->InstdAmt->addAttribute('Ccy', 'EUR');

        $Xml->addChild('DrctDbtTx')->addChild('MndtRltdInf'); /* ISO: 2.46, 2.47 */
        $Xml->DrctDbtTx->MndtRltdInf->addChild('MndtId', $this->getTransactionIdentifier() ); /* ISO: 2.48 */
        $Xml->DrctDbtTx->MndtRltdInf->addChild('DtOfSgntr', $this->getSignatureDate()); /* ISO: 2.49 */

        $Xml->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('BIC', $this->getDebtorBic());

        $Xml->addChild('Dbtr')->addChild('Nm', $this->getDebtorName() ); /* ISO: 2.72 */
        $Xml->addChild('DbtrAcct')->addChild('Id')->addChild('IBAN', $this->getDebtorIban()); /* ISO: 2.73 */
        $Xml->addChild('RmtInf')->addChild('Ustrd', $this->getTransactionDescription()); /* ISO: 2.89 */

        return $SimpleXmlElement;
    }

    /**
     * Return the transaction as partial XML (For debugging purouses)
     *
     * @return string
     */
    public function asXml() {
        $Xml = simplexml_load_string('<Transaction></Transaction>');
        return $this->appendToXml($Xml)->asXml();
    }





}

?>
