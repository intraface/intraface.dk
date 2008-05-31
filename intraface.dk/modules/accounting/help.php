<?php
require($_SERVER['DOCUMENT_ROOT'] . '/include_first.php');

$kernel->module('accounting');

$page = new Intraface_Page($kernel);
$page->start('Hjælp til bogføring');
?>
<h1>Om at lave regnskab</h1>

<h2>Dobbelt bogholderi</h2>

<p>Dette regnskab er et <em>dobbelt bogholderi</em>. Det betyder, at du bogfører alle beløb flere gange. I praksis posterer du som regel først på en resultat-konto og derefter på en status- og moms-konto.</p>

<h2>Kontoplan</h2>

<p>For at bogføre skal du have en kontoplan. Kontoplanen er delt i to dele:</p>

<dl>
	<dt>Resultat</dt>
	<dd>Her beregnes den skattepligtige indkomst. Her skal alle skattepligtige indtægter som fx salgsfakturaer, kontantsalg, renteindtægter, modtagne rabatter bogføres. Samtidig skal alle fradaragsberettigede udgifter som varekøb, lønninger, kontorudgifter også bogføres. Forskellen mellem indtægter og udgifter er periodens resultat.</dd>
	<dt>Status</dt>
	<dd>Her findes al værdi og gæld i firmaet.</dd>
</dl>

<p>Status er igen delt op i to dele:</p>

<dl>
	<dt>Aktiver</dt>
	<dd>Her findes alle værdier i firmaet. Fx debitorernes udestående, kassens indhold, indestående i banken, varelageret og driftsmidler (fx biler, værdipapirer og ejendomme).</dd>
	<dt>Passiver</dt>
	<dd>Her findes gælden og egenkapitalen. Gæld kan fx være kreditorer, skyldig skat og moms, gæld i ejendomme.</dd>
</dl>

<h2>Egenkapital</h2>

<p>Egenkapitalen er forskellen mellem aktiver og passiver, og egenkapitalen står som et negativt tal i regnskabet. Egenkapitalen kan forklares som virksomhedens gæld til indehaveren.</p>

<!--
DEBET/KREDIT kan virke lidt svært i begyndelsen, men her er nogle enkle huskeregler: - - DEBET=Plus/Positiv og KREDIT=Minus/Negativ. I "RESULTAT" er Indtægter=Kredit og Udgifter=Debet, og i "STATUS" er "AKTIVER"=Debet og "PASSIVER"=Kredit. Hvis du er i tvivl når du posterer i "RESULTAT" , så tænk på hvor der skal modposteres i "STATUS", her er du ikke i tvivl om, at indgået beløb skal Debiteres/Plus i kassen, og at udgået beløb skal Krediteres/Minus i kassen, - -ja så skal det naturligvis posteres modsat i "RESULTAT", her er et par eksempler

Finans Konteringer



Bemærk!  - - - For at få penge i kassen skal du sælge noget, her har vi solgt for( Omsat for) kr. 100,00 i april måned,  - - beløbet er "KREDITERET" konto nr. 1060, som er en salgskonto/RESULTATkonto,  beløbet modkonteres på konto 6810 som er Kassen/STATUS aktiver. Saldo på konto 6810/kassen udviser nu et positivt beløb kr. 100,00. I bogføringen er dette beløb posteret i "DEBET" siden. - - - - Salget er posteret i "KREDIT" siden og salgskonto 1060 udviser nu en saldo Kr. 80,00, idet der er beregnet moms Kr. 20,00 af salgsbeløbet. Den post du bogfører bliver således posteret 3 gange. Du har ved oprettelse af kontoplaner bestemt hvilke konto momsen skal afløftes på (her 8720). når du taster "KONTER", bliver konto 1060 krediteret Kr. 80,00 - - konto 6810 debeteres med kr. 100,00 og momskontoen som er en STATUS/PASSIVER konto krediteres med momsen kr. 20,00-----Nu er der Debetereet i alt kr. 100,00 og krediteret i alt kr. 100,00.

Den næste post hvor der er købt en fl. Whisky, posteres på nøjagtig samme måde, bare modsat idet et varekøb skal "DEBETERES".  du vil også kunne se at Kassebeholdningen/Saldo på konto 6810 nu igen går i 0 idet der er købt for det samme som du har solgt og du har således ikke tjent nogen penge til kassen, SE KONTOPLAN
-->

<h2>Kontoplan</h2>

<p>Inden du bogfører skal du bruge en kontoplan. Hvis du ikke har erfaring med kontoplaner, bør du rådføre dig med en autoriseret revisor. I hvert fald skal du tænke dig rigtig grundigt om, inden du starter med at bogføre.</p>

<h3>Momskonti</h2>

<p>Hvis du er momsregistreret, skal der være momskonti i dit regnskab. Som minium bør du have en konto til indgående moms (købsmoms) og udgående moms (salgsmoms) og en konto til momsbetalinger (de indbetalinger der er foretaget til Skat). Disse konti bør være grupperet sammen i regnskabet.</p>

<h2>Regler</h2>

<dl>
	<dt>Debet og kredit</dt>
		<dd>Beløb indtastes med moms. Debet er altid venstre side, og kredit højre side. Debet er altid modtagersiden, og kredit er altid afgiversiden.</dd>
	<dt>Bogføringsfrekvens</dt>
		<dd>Som udgangspunkt har du pligt til at holde din bogføring ajour hver dag.</dd>
	<dt>Nummerering af bilag og fakturaer</dt>
		<dd>Bilag og fakturaer skal nummereres fortløbende. De må gerne have hver deres nummerserie. Nummereringen må gerne starte forfra, når du starter et nyt år.</dd>
	<dt>Fakturaer</dt>
		<dd>Fakturaer skal bogføres på fakturadatoen. Hvis kunden ikke betaler med det samme skal de modposteres på en debitorkonto.</dd>
</dl>

<h2>Bogføring</h2>
<dl>
	<dt>Indtægter</dt>
		<dd>Du skal lave en faktura på alt salg - med mindre du har et kasseapparat. Hvis beløbet er over 750 kroner, så skal fakturaen indeholde modtagerens navn og adresse.</dd>
	<dt>Bogfør indtægter</dt>
		<dd>En indtægt krediteres på indtægtskontoen, og debiteres på den konto, hvor pengene modtages. Det kan fx være kassen, banken eller debitor (som er reserveret til folk der ikke betaler med det samme).</dd>
	<dt>Bogfør udgifter</dt>
		<dd>Hvis du har en udgift, skal den debiteres på den konto, som bedst beskriver, hvad du har købt, og den skal krediteres i fx banken eller kassen.</dd>
	<dt>Betalende debitorer</dt>
		<dd>Hvis du har en debitor, der betaler, skal pengene debiteres i kassen eller banken og krediteres på debitorkontoen.</dd>
</dl>

<?php
$page->end();
?>