<?php
/*
 * This filter will be added to the basket to do various actions on the basket such as adding product
 *
 * 




filter_index: number for the running order
evaluate_key: price, weight, webshop_coupon (later: (array)product_id, customer_id ...) 
evaluate_method: > < == != [maybe (>= <=) ??]
evaluate_value: the number or value e.g. 600 if price. 
go_to_index_after: makes i possible to jump further in the filter.

action_key: no_action, add_product_id, (later: add_order_text, ...?) 
action_value: id or text
action_amount: number of times the action e.g 10 x product_id
action_unit: quantity or percentage. 











 */
 
 
 class WebshopFilter extends Standard {
 	
 	
 	function _construct() {
 		
 	}
 	
 	
 	
 }
?>
