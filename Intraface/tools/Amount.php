<?php

/**
 * Bruges til at konvertere beløb til og fra database
 * Bør den ikke kunne bruges de enkelte funktioner
 * direkte. Det vil i hvert fald gøre den mere anvendelig
 * i selve processen?
 * @author Sune
 * @version 001
 */
class Amount {
	var $amount;
	
	/**
	 * indskriv det beløb det drejer sig om
	 * @param amount double beløb 
	 */
	function amount($amount) {
		$this->amount = $amount;
	}
	
	/**
	 * Public: Konvertere et dansk beløb til engelsk
	 * Bør der ikke være noget der validerer?
	 * @return 1
	 */
	function convert2db() {
		$this->amount = str_replace(".", "", $this->amount);
		$this->amount = str_replace(",", ".", $this->amount);
		settype($this->amount, "double");
		return(1);
	}
	
	
	/**
	 * Public: konvertere et engelsk beløb til et dansk
	 * Bør vist skrives om. Den returnerer jo 1 uanset?
	 * @ return 1
	 */
	function convert2dk() {		 
		//if(is_double($this->amount)) {
			$this->amount = number_format($this->amount, 2, ",", ".");
		//}
		return(1); 	
	}
	
	/**
	 * Public: henter beløbet efter konvertering
	 * @return beløb
	 */
	function get() {
		return($this->amount);
	}
}
?>