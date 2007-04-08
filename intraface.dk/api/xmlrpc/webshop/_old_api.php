<?php 
require('../../include_first.php'); 
require('3Party/Template/Template.php');

$tpl = new Template(PATH_TEMPLATE);
$tpl->set('title', 'Webshop XML-RPC API - API for at bruge webshoppen');
$tpl->set('content_main', '

<h1>Webshop, XML-RPC API</h1>

<h2>API til webshop</h2>

<p id="author">Af Lars Olesen, redigeret 9. juli 2006.</p>

<h2 id="table_of_contents">Indholdsfortegnelse</h2>

<ul>
	<li><a href="#Status">Status for dette dokument</a></li>
	<li><a href="#Xmlrpc">Om XML-RPC</a></li>  
	<li><a href="#About">Om webshoppen</a></li>
	<li><a href="#Server">Server information</a></li>
	<li><a href="#Methods">Metoder</a></li>
	<li><a href="#Struct">Structs</a>
		<ul>
			<li><a href="#struct_credentials">credentials</a></li>
		</ul>
	</li>
</ul>

<h2 id="Status">Status for dette dokument</h2>

<p>Kladde, dokumentet er stadig under udvikling.</p>

<h2 id="Xmlrpc">Om XML-RPC</h2>

<p>Vi foreslår, at du bruger den klasse, du kan finde på www.incutio.com.</p>

<h2 id="About">Om webshoppen</h2>

<p>Denne XML RPC-API kan bruges, hvis du benytter systemet onlinefaktura.dk. APIen gør det muligt at tilgå oplysningerne i systemet til brug på din egen webshop.</p>


<h2 id="Server">Server info</h2>
<p>
	<strong>Address:</strong> http://www.intraface.dk/xmlrpc/WebshopServer.php<br />
	<strong>Port:</strong> 80
</p>
    
<h2 id="Methods">Metoder</h2>

<h3 id="Product">Product methods</h3>

<h4>product.getList</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>string</em> search</p>
<p><strong>Returns:</strong> <em>array</em> with a product list.</p>

<h4>product.getProductsByKeyword</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>array</em> keyword_id</p>
<p><strong>Returns:</strong> <em>array</em> with a product list.</p>

<h4>product.getProductKeywords</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials</p>
<p><strong>Returns:</strong> <em>array</em> with a keywords.</p>
  
<h4>product.getRelatedProducts</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>integer</em> product_id</p>
<p><strong>Returns:</strong> <em>array</em> with a product list.</p>

<h4>product.get</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>integer</em> product_id</p>
<p><strong>Returns:</strong> <em>array</em> with the product</p>

<h3 id="Post">Basket methods</h3>

<h4>basket.add</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>integer</em> product_id</p>
<p><strong>Returns:</strong> <code>true</code> on success.</p>

<h4>basket.remove</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>integer</em> product_id</p>
<p><strong>Returns:</strong> <code>true</code> on success.</p>

<h4>basket.change</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials, <em>integer</em> product_id, <em>integer</em> quantity</p>
<p><strong>Returns:</strong> <code>true</code> on success.</p>

<h4>basket.totalPrice</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials</p>
<p><strong>Returns:</strong> <em>float</em> amount.</p>

<h4>basket.totalWeight</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials</p>
<p><strong>Returns:</strong> <em>integer</em> weight (in g).</p>
 
<h4>basket.getItems</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials</p>
<p><strong>Returns:</strong> <em>array</em> with the items in the basket.</p>

<h3 id="Order">Order methods</h3>    

<h4>basket.placeOrder</h4>
<p><strong>Parameters:</strong> <em>struct</em> credentials</p>
<p><strong>Returns:</strong> <em>array</em> with the items in the basket.</p>

<h2 id="Struct">Structs</h2>

<h3 id="struct_credentials"><em>struct</em> credentials</h3>

<table class="struct">
	<thead>
		<tr>
			<th>Type</th>
			<th>Member name</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><em>string</em></td>
			<td>private_key</td>
		</tr>
		<tr>
			<td><em>string</em></td>
			<td>session_id</td>
		</tr>								

	</tbody>
</table>

');

echo $tpl->fetch('api/main-tpl.php');
?>