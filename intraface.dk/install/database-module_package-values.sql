INSERT INTO `module_package_group` ( `id` , `name` )
VALUES (
'1', 'Hjemmeside'
), (
'2', 'Bogføring'
), (
'3', 'Virksomhedsstyring'
);

INSERT INTO `module_package` ( `id` , `module_package_group_id` , `name` , `product_id` )
VALUES (
'1', '1', 'Gratis', '0'
), (
'2', '1', 'Mellem', '0'
), (
'3', '1', 'Stor', '0'
), (
'4', '2', 'Gratis', '0'
), (
'5', '2', 'Mellem', '0'
), (
'6', '2', 'Stor', '0'
), (
'7', '3', 'Gratis', '0'
), (
'8', '3', 'Mellem', '0'
), (
'9', '3', 'Stor', '0'
);


INSERT INTO `module_package_module` ( `id` , `module_package_id` , `module` , `limiter` )
VALUES (
'1', '1', 'cms', ''
), (
'2', '1', 'filemanager', ''
), (

'3', '2', 'cms', ''
), (
'4', '2', 'filemanager', ''
), (

'5', '3', 'cms', ''
), (
'6', '3', 'filemanager', ''
), (



'7', '4', 'accounting', ''
), (

'8', '5', 'accounting', ''
), (

'9', '6', 'accounting', ''
), (


'10', '7', 'debtor', ''
), (
'11', '7', 'quotation', ''
), (
'12', '7', 'order', ''
), (
'13', '7', 'invoice', ''
), (
'14', '7', 'contact', ''
), (
'15', '7', 'product', ''
), (
'16', '7', 'email', ''
), (
'17', '7', 'procurement', ''
), (
'18', '7', 'stock', ''
), (

'19', '8', 'debtor', ''
), (
'20', '8', 'quotation', ''
), (
'21', '8', 'order', ''
), (
'22', '8', 'invoice', ''
), (
'23', '8', 'contact', ''
), (
'24', '8', 'product', ''
), (
'25', '8', 'email', ''
), (
'26', '8', 'procurement', ''
), (
'27', '8', 'stock', ''
), (

'28', '9', 'debtor', ''
), (
'29', '9', 'quotation', ''
), (
'30', '9', 'order', ''
), (
'31', '9', 'invoice', ''
), (
'32', '9', 'contact', ''
), (
'33', '9', 'product', ''
), (
'34', '9', 'email', ''
), (
'35', '9', 'procurement', ''
), (
'36', '9', 'stock', ''


);
