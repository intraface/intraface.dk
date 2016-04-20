<?php
/**
 * Return OIOXML files when given a Debtor
 *
 * Example usage implementing the Visitor pattern
 *
 * <code>
 * $debtor = Debtor::factory('invoice', 10);
 * $visitor = new Debtor_Report_OIOXML();
 * $debtor->accept($visitor);
 * echo $visitor->display();
 * </code>
 *
 * This gives a good example on the methods we need in debtor
 * to make everything readable in the code - for instance.
 *
 * <code>
 * $debtor->getCurrencyCode();
 * $debtor->getContact();
 * $debtor->getContactPerson();
 * $debtor->getPaymentInfo();
 * $debtor->getTotal();
 * $debtor->getTaxTotal();
 * </code>
 *
 * @package Intraface_Debtor
 * @author  Lars Olesen <lars@legestue.net>
 * @since   1.0
 * @version 1.0
 * @see     http://www.oio.dk/dataudveksling/ehandel/eFaktura/eksempler
 */

class Debtor_Report_OIOXML
{
    private static function httpHeader()
    {
        header('Content-Type: text/xml; charset=iso-8859-1');
    }

    private function start()
    {
        $this->output .= '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n";
        $this->output .= '<Invoice xmlns="http://rep.oio.dk/ubl/xml/schemas/0p71/pie/" xmlns:com="http://rep.oio.dk/ubl/xml/schemas/0p71/common/" xmlns:main="http://rep.oio.dk/ubl/xml/schemas/0p71/maindoc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://rep.oio.dk/ubl/xml/schemas/0p71/pie/ http://rep.oio.dk/ubl/xml/schemas/0p71/pie/pieStrict.xsd">' . "\n";
    }

    private function end()
    {
        $this->output .= '</Invoice>';
    }

    public function output($debtor)
    {
        $this->start();
        $this->output .= '<com:ID>'.$debtor->get('id').'</com:ID>' . "\n";
        $this->output .= '<com:IssueDate>'.$debtor->get('date_due').'</com:IssueDate>';
        $this->output .= '<com:TypeCode>PIE</com:TypeCode>';
        $this->output .= '<main:InvoiceCurrencyCode>DKK</main:InvoiceCurrencyCode>';
        $this->output .= '<com:BuyersReferenceID>'.$debtor->contact->get('ean').'</com:BuyersReferenceID>';

        // referenced order
        $this->output .= '<com:ReferencedOrder>';
        $this->output .= '	<com:BuyersOrderID></com:BuyersOrderID>';
        $this->output .= '	<com:SellersOrderID/>';
        $this->output .= '	<com:IssueDate>'.$debtor->get('this_date').'</com:IssueDate>';
        $this->output .= '</com:ReferencedOrder>';

        // buyer
        $this->output .= '<com:BuyerParty>';
        $this->output .= '	<com:ID schemeID="CVR">'.$debtor->contact->get('cvr').'</com:ID>';
        $this->output .= '	<com:AccountCode></com:AccountCode>';
        $this->output .= '	<com:PartyName>';
        $this->output .= '		<com:Name>'.$debtor->contact->get('name').'</com:Name>';
        $this->output .= '	</com:PartyName>';
        $this->output .= '	<com:Address>';
        $this->output .= '		<com:ID>Fakturering</com:ID>';
        $this->output .= '		<com:Street>'.$debtor->contact->get('address').'</com:Street>';
        $this->output .= '		<com:HouseNumber></com:HouseNumber>';
        $this->output .= '		<com:CityName>'.$debtor->contact->get('city').'</com:CityName>';
        $this->output .= '		<com:PostalZone>'.$debtor->contact->get('postcode').'</com:PostalZone>';
        $this->output .= '		<com:Country>';
        $this->output .= '			<com:Code>DK</com:Code>';
        $this->output .= '		</com:Country>';
        $this->output .= '	</com:Address>';
        $this->output .= '	<com:BuyerContact>';
        $this->output .= '		<com:ID>'.$debtor->contact->get('email').'</com:ID>';
        $this->output .= '	</com:BuyerContact>';
        $this->output .= '</com:BuyerParty>';

        // seller
        $this->output .= '<com:SellerParty>';
        $this->output .= '	<com:ID schemeID="CVR">'.$debtor->kernel->intranet->address->get('cvr').'</com:ID>';
        $this->output .= '	<com:PartyName>';
        $this->output .= '		<com:Name>'.$debtor->kernel->intranet->get('name').'</com:Name>';
        $this->output .= '	</com:PartyName>';
        $this->output .= '	<com:Address>';
        $this->output .= '		<com:ID>Betaling</com:ID>';
        $this->output .= '		<com:Street>'.$debtor->kernel->intranet->address->get('address').'</com:Street>';
        $this->output .= '		<com:HouseNumber></com:HouseNumber>';
        $this->output .= '		<com:CityName>'.$debtor->kernel->intranet->address->get('city').'</com:CityName>';
        $this->output .= '		<com:PostalZone>'.$debtor->kernel->intranet->address->get('postcode').'</com:PostalZone>';
        $this->output .= '	</com:Address>';
        $this->output .= '	<com:PartyTaxScheme>';
        $this->output .= '		<com:CompanyTaxID schemeID="CVR">'.$debtor->kernel->intranet->address->get('cvr').'</com:CompanyTaxID>';
        $this->output .= '	</com:PartyTaxScheme>';
        $this->output .= '</com:SellerParty>';

        // payment
        $this->output .= '<com:PaymentMeans>';
        $this->output .= '	<com:TypeCodeID>null</com:TypeCodeID>';
        $this->output .= '	<com:PaymentDueDate>'.$debtor->get('date_due').'</com:PaymentDueDate>';
        $this->output .= '	<com:PaymentChannelCode>KONTOOVERFØRSEL</com:PaymentChannelCode>';
        $this->output .= '	<com:PayeeFinancialAccount>';
        $this->output .= '		<com:ID>1111111111</com:ID>';
        $this->output .= '		<com:TypeCode>BANK</com:TypeCode>';
        $this->output .= '		<com:FiBranch>';
        $this->output .= '			<com:ID>1111</com:ID>';
        $this->output .= '			<com:FinancialInstitution>';
        $this->output .= '				<com:ID>null</com:ID>';
        $this->output .= '				<com:Name>Sparakassen</com:Name>';
        $this->output .= '			</com:FinancialInstitution>';
        $this->output .= '		</com:FiBranch>';
        $this->output .= '	</com:PayeeFinancialAccount>';
        $this->output .= '	<com:PaymentAdvice>';
        $this->output .= '		<com:AccountToAccount>';
        $this->output .= '			<com:PayerNote>2296</com:PayerNote>';
        $this->output .= '		</com:AccountToAccount>';
        $this->output .= '	</com:PaymentAdvice>';
        $this->output .= '</com:PaymentMeans>';

        // taxes
        $this->output .= '<com:TaxTotal>';
        $this->output .= '	<com:TaxTypeCode>VAT</com:TaxTypeCode>';
        $this->output .= '	<com:TaxAmounts>';
        $this->output .= '		<com:TaxableAmount currencyID="DKK">'.$debtor->get('total').'</com:TaxableAmount>';
        $this->output .= '		<com:TaxAmount currencyID="DKK"></com:TaxAmount>';
        $this->output .= '	</com:TaxAmounts>';
        $this->output .= '	<com:CategoryTotal>';
        $this->output .= '		<com:RateCategoryCodeID>VAT</com:RateCategoryCodeID>';
        $this->output .= '		<com:RatePercentNumeric>25</com:RatePercentNumeric>';
        $this->output .= '		<com:TaxAmounts>';
        $this->output .= '			<com:TaxableAmount currencyID="DKK">5300.00</com:TaxableAmount>';
        $this->output .= '			<com:TaxAmount currencyID="DKK">1325.00</com:TaxAmount>';
        $this->output .= '		</com:TaxAmounts>';
        $this->output .= '	</com:CategoryTotal>';
        $this->output .= '</com:TaxTotal>';

        // totaler
        $this->output .= '<com:LegalTotals>';
        $this->output .= '	<com:LineExtensionTotalAmount currencyID="DKK">5300.00</com:LineExtensionTotalAmount>';
        $this->output .= '	<com:ToBePaidTotalAmount currencyID="DKK">6625</com:ToBePaidTotalAmount>';
        $this->output .= '</com:LegalTotals>';

        // invoice lines
        $i = 1;
        foreach ($debtor->getItems() as $item) {
            $this->output .= '<com:InvoiceLine>';
            $this->output .= '	<com:ID>'.$i.'</com:ID>';
            $this->output .= '	<com:InvoicedQuantity unitCode="'.$item['unit'].'" unitCodeListAgencyID="n/a">'.$item['quantity'].'</com:InvoicedQuantity>';
            $this->output .= '	<com:LineExtensionAmount currencyID="DKK">'.$item['amount'].'</com:LineExtensionAmount>';
            $this->output .= '	<com:Item>';
            $this->output .= '		<com:ID>'.$item['id'].'</com:ID>';
            $this->output .= '		<com:Description>'.$item['description'].'</com:Description>';
            $this->output .= '	</com:Item>';
            $this->output .= '	<com:BasePrice>';
            $this->output .= '		<com:PriceAmount currencyID="DKK">'.$item['price'].'</com:PriceAmount>';
            $this->output .= '	</com:BasePrice>';
            $this->output .= '</com:InvoiceLine>';
            $i++;
        }

        $this->end();

        $this->httpHeader();
        return $this->output;
    }
}


/*

<Invoice xmlns="http://rep.oio.dk/ubl/xml/schemas/0p71/pie/" xmlns:com="http://rep.oio.dk/ubl/xml/schemas/0p71/common/" xmlns:main="http://rep.oio.dk/ubl/xml/schemas/0p71/maindoc/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://rep.oio.dk/ubl/xml/schemas/0p71/pie/
http://rep.oio.dk/ubl/xml/schemas/0p71/pie/pieStrict.xsd">
  <com:ID>2296</com:ID>
  <com:IssueDate>2004-10-13</com:IssueDate>
  <com:TypeCode>PIE</com:TypeCode>
  <main:InvoiceCurrencyCode>DKK</main:InvoiceCurrencyCode>
  <com:BuyersReferenceID>5798000416642</com:BuyersReferenceID>

  <com:ReferencedOrder>
    <com:BuyersOrderID>M23-453</com:BuyersOrderID>
    <com:SellersOrderID/>
    <com:IssueDate>2004-10-01</com:IssueDate>
  </com:ReferencedOrder>
  <com:BuyerParty>
    <com:ID schemeID="CVR">12312312</com:ID>

    <com:AccountCode>3070</com:AccountCode>
    <com:PartyName>
      <com:Name>IT- og Telestyrelsen</com:Name>
    </com:PartyName>
    <com:Address>
      <com:ID>Fakturering</com:ID>
      <com:Street>Holsteinsgade</com:Street>

      <com:HouseNumber>63</com:HouseNumber>
      <com:CityName>K�benhavn �.</com:CityName>
      <com:PostalZone>2100</com:PostalZone>
      <com:Country>
        <com:Code>DK</com:Code>
      </com:Country>
    </com:Address>

    <com:BuyerContact>
      <com:ID>rak@itst.dk</com:ID>
    </com:BuyerContact>
  </com:BuyerParty>
  <com:SellerParty>
    <com:ID schemeID="CVR">22222222</com:ID>
    <com:PartyName>
      <com:Name>Company name A/S</com:Name>

    </com:PartyName>
    <com:Address>
      <com:ID>Betaling</com:ID>
      <com:Street>Jernbanegade</com:Street>
      <com:HouseNumber>875</com:HouseNumber>
      <com:CityName>Roskilde</com:CityName>
      <com:PostalZone>4000</com:PostalZone>

    </com:Address>
    <com:PartyTaxScheme>
      <com:CompanyTaxID schemeID="CVR">22222222</com:CompanyTaxID>
    </com:PartyTaxScheme>
  </com:SellerParty>
  <com:PaymentMeans>
    <com:TypeCodeID>null</com:TypeCodeID>
    <com:PaymentDueDate>2004-11-01</com:PaymentDueDate>

    <com:PaymentChannelCode>KONTOOVERF�RSEL</com:PaymentChannelCode>
    <com:PayeeFinancialAccount>
      <com:ID>1111111111</com:ID>
      <com:TypeCode>BANK</com:TypeCode>
      <com:FiBranch>
        <com:ID>1111</com:ID>
        <com:FinancialInstitution>

          <com:ID>null</com:ID>
          <com:Name>Sparakassen</com:Name>
        </com:FinancialInstitution>
      </com:FiBranch>
    </com:PayeeFinancialAccount>
    <com:PaymentAdvice>
      <com:AccountToAccount>
        <com:PayerNote>2296</com:PayerNote>

      </com:AccountToAccount>
    </com:PaymentAdvice>
  </com:PaymentMeans>
  <com:TaxTotal>
    <com:TaxTypeCode>VAT</com:TaxTypeCode>
    <com:TaxAmounts>
      <com:TaxableAmount currencyID="DKK">5300.00</com:TaxableAmount>
      <com:TaxAmount currencyID="DKK">1325.00</com:TaxAmount>

    </com:TaxAmounts>
    <com:CategoryTotal>
      <com:RateCategoryCodeID>VAT</com:RateCategoryCodeID>
      <com:RatePercentNumeric>25</com:RatePercentNumeric>
      <com:TaxAmounts>
        <com:TaxableAmount currencyID="DKK">5300.00</com:TaxableAmount>
        <com:TaxAmount currencyID="DKK">1325.00</com:TaxAmount>

      </com:TaxAmounts>
    </com:CategoryTotal>
  </com:TaxTotal>
  <com:LegalTotals>
    <com:LineExtensionTotalAmount currencyID="DKK">5300.00</com:LineExtensionTotalAmount>
    <com:ToBePaidTotalAmount currencyID="DKK">6625</com:ToBePaidTotalAmount>
  </com:LegalTotals>
  <com:InvoiceLine>

    <com:ID>1</com:ID>
    <com:InvoicedQuantity unitCode="stk." unitCodeListAgencyID="n/a">100</com:InvoicedQuantity>
    <com:LineExtensionAmount currencyID="DKK">300</com:LineExtensionAmount>
    <com:Item>
      <com:ID>4523</com:ID>
      <com:Description>Kuglepenne med logo</com:Description>

    </com:Item>
    <com:BasePrice>
      <com:PriceAmount currencyID="DKK">3</com:PriceAmount>
    </com:BasePrice>
  </com:InvoiceLine>
  <com:InvoiceLine>
    <com:ID>2</com:ID>
    <com:InvoicedQuantity unitCode="kasse" unitCodeListAgencyID="n/a">50</com:InvoicedQuantity>

    <com:LineExtensionAmount currencyID="DKK">5000</com:LineExtensionAmount>
    <com:Item>
      <com:ID>4533</com:ID>
      <com:Description>Brevpapir med logo - Kasse med 1000 ark.</com:Description>
    </com:Item>
    <com:BasePrice>
      <com:PriceAmount currencyID="DKK">100</com:PriceAmount>

    </com:BasePrice>
  </com:InvoiceLine>
</Invoice>

 */
