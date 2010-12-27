<?php
/**
 * Class for all English product texts.
 * @author sune
 *
 */
class Intraface_modules_product_Controller_Show_CarmakomaProductText
{
    
    /**
     * Returns text.
     * 
     * 
     * @param $langugage language da or en
     * @param $id id og product
     * @param $default default text
     * @return string
     */
    public function get($language, $id, $default){
        if ($language == 'en') {
            $text = $this->getEnglishText($id);
            if($text !== NULL) {
                return $text;
            }
        }
        return $default;
    }
    
    private function getEnglishText($id)
    {
        
        $tr = array(
        7990  => 'Super nice knitwear top with deep v-neck cut, silver lyrex hems and great loose-fitting sleeves. Wrinkly effect lengthwise on front and back.

Styling tips: Style with leggings, e.g. style Luxx, and a belt around the waist, e.g. style Rollercoaster.

Fit: Loose and floating.
Length: Sizes: XS: 71 cm/ S: 72 cm/ M: 73 cm /L: 74 cm

Fabric: 55% viscose 45% cotton',

        7996 => 'Super nice and soft knitwear quality. Jersey with many stylish details. Big buttons down the sleeve, great neck opening. Difference in length between front and back. An absolute must for this autumn.

Styling tips: Style with leggings, e.g. style Luxx, a wide belt around the waist, e.g. style Rollercoaster. Or wear the jersey with jeans.

Fit: Beautiful and loose fit.

Fabric: 55% viscose 45% cotton',

        7979 => 'Trendy and stylish dress in satin with wrinkly detail below chest and on back.

Styling tips: Style with leggings, e.g. style Luxx, a wide belt around the waist, e.g. style Rollercoaster. Or wear the dress over a pair of jeans.

Fit: Fancy floating fit.

Length: Sizes: XS: 85cm/ S: 86,5cm / M: 88cm /L: 89,5cm

Fabric: (Satin) 3% elastane 97% polyester.',

        7999  => 'Cool jersey blouse with zip on front. Many great details. High neck with oversize press studs. Popper mechanism on sleeves that gives opportunity of lengthening or shortening the sleeves. Zip pockets with synthetic leather laces. Wrinkly effect on front and back.

Styling tips: Combine the jacket with a tank top longer than the jacket. Wear with jeans. Put a belt around the waist, e.g. style Rollercoaster.

Fit: Casual fit.
Length Sizes: XS: 69cm/ S: 70,5cm / M: 72cm /L: 73,5cm

Fabric: Nice and soft quality 95% cotton 5% spandex',

        8000 => 'Cool jacket in great quality with three-quarter sleeves. Big collar, wrinkly detail below chest. Adjustable buckle detail on sleeves.

Styling tips: The jacket looks super cool with leggings, e.g. style Luxx. Can be worn with a wide belt around the waist, e.g. style Rollercoaster.

Fit: Floating fit.

Jacket length: Sizes XS: 92 cm /S: 93,5cm/ M: 95cm/ L: 94cm',

        8002 => 'A trendy shirt dress for weekdays or parties. Zip on front. Metal press studs on the wide cuffs. Smock detail on back, side pockets and balloon effect below waist.

Styling tips: Style the shirt with a pair of leggings, e.g. style luxx, and pumps. Or wear it over jeans.

Fit: Great and stylish fit. The shirt gives a slim, curvy figure.

Length: Sizes: XS: 97 cm/ S: 98,5cm / M: 100cm /L: 101,5 cm

Fabric: 97% cotton 3% spandex',

        8003 => 'Nice knitwear dress with silver lyrex detail on top part of dress. Wrinkly details on front and back and on cuffs. Beautiful and wide neck opening.

Styling tips: Style the dress with a belt around the waist, e.g. style Rollercoaster, and wear it over leggings e.g. style Luxx.

Fit: Stylish and elegant loose-fit.

Length: Sizes: XS: 89cm/ S: 90,5cm / M: 92cm /L: 93,5cm

Fabric: 55% viscose 45% cotton',

        8004 => 'Cool cardigan sweater with three-quarter sleeves, asymmetrically placed metal press studs in oversize look at the neck. Only the four first button are visible. Press stud detail on back. Laces below waist give a stylish, trendy look.

Styling tips: The cardigan can also be worn as a dress. Style it with leggings, e.g. style Luxx.

Fit: Casual fit.
Length: Sizes: XS: 88cm/ S: 89,5cm / M: 91cm / L: 92,5cm

Fabric: Nice and soft quality: 95% cotton 5% spandex',

        7927 => 'Hot party top with many details. Wrinkly effect below chest. Beautiful neck opening on front and back with stylish buttons on back.

Styling tips: Style the top with a wide belt around the waist, e.g. style Rollercoaster. Wear with jeans.

Fit: Floating fit.
Length: Sizes: XS: 71 cm/ S: 72,5cm / M: 74cm /L: 75,5 cm

Fabric: (Satin) 3% elastane 97% polyester.',

        8005 => 'Trendy leggings in shining quality.
High crotch line places the elastic band around waistline. With extra length to give wrinkly effect around shank.

Styling tips: Leggings look cool with anything. Dresses, oversize shirts. It is a super hot look.

Fit: Tight fit.
Length: 82 cm in all sizes.

Fabric: 90% polyamide 10% elastane',

        8006 => 'Nice and trendy wool cape with many details. Elegant high collar and beautiful, big buttons. The cape is lined with black lining. Closes up in front with visible, oversize buttons on top and unnoticed press studs below so the cape keeps closed. Nice, big pockets and super nice button details on back. A MUST HAVE for this autumn/winter.

Styling tips: Really cool with a pair of long leather gloves.

Fit: Nice floating fit.
Cape length: Sizes: XS: 80cm / S: 81,5cm / M: 83cm / L: 84,5cm

Fabric: Wool mix',

        8010 => 'Trendy tight-fitting strapless dress with wide, metal buckled belt. Closes up with oversize metal press studs. Elastic panel on back that makes the dress flexible and gives a hot fit. The dress is lined with black satin.

Styling tips: Style the dress with leggings, e.g. style Luxx. Wear with a short, tough-looking jacket, e.g. style Sevillia. This look is cool and a must have for autumn 2008.

Fit: Tight fit.

Dress length (Side measure): Sizes: XS: 72cm/ S: 73,5cm / M: 75cm /L: 76,5cm

Fabric: 97% cotton 3% spandex. Lining: satin.',

        7980 => 'Wrap-around belt in beautiful and soft synthetic leather quality.

Styling tips: A stylish belt is a must have for the wardrobe. Wear the belt with most of our dresses and tops.

Fit: Full length: 305 cm, to be winded around waist.

Size: ONE SIZE

Fabric: Synthetic leather',

        7995 => 'Trendy and cool jacket in super quality with many great details. Hot neck opening and zip on front that gives a tough look. Wrinkly effect alongside the zip gives an incredibly beautiful silhouette. Rib hems at neck and waist. Wide rib at wrists. The jacket is a must have for autumn/winter.

Styling tips: Create a trendy look by styling the jacket with a dress, e.g. style Pisa, and leggings, e.g. style Luxx.

Fit: Super hot and stylish fit. Tight-fitting, but loose enough for good mobility.
Length: Sizes: XS: 67cm/ S: 68,5cm / M: 70cm / L: 71,5 cm

Fabric: Nylon',

        7997 => 'Stylish, big shirt with belt. Beautiful, wide collar. Nice detail with press studs on front and on cuffs.

Styling tips: Style with leggings, e.g. style Luxx. Wear with short, tough-looking jacket, e.g. style Sevillia.

Fit: Floating.
Length: Sizes: XS: 87cm/ S: 88,5cm / M: 90cm /L: 91,5cm

Fabric: 97% cotton 3% spandex',

        8012 => 'Super nice party dress in strong jersey. Front piece is decorated with black sequins. Boat neck cut with rib hems. Can be worn asymmetrically with one shoulder showing for a younger look. No sequins on the back for a hotter look.

Styling tips: Combine the dress with a wide belt around the waist, e.g. style Rollercoaster, and wear with leggings, e.g. style Luxx.

Fit: floating.
Dress length: Sizes: XS: 95cm/ S: 96cm / M: 98cm /L: 99,5cm

Fabric: Nice soft jersey 95% cotton 5% spandex.',

        8011 => 'Cool sweater dress in 80\'s style with many details. Beautiful neck opening and stylish detail with asymmetrically placed press studs. Wrinkly effect on back gives a great figure.

Stylings tip: Style with leggings, e.g. style Luxx.

Fit: Casual fit.
Length: Sizes: XS: 89cm/ S: 90,5cm / M: 92cm /L: 93,5cm.

Fabric: Nice and soft quality: 95% cotton 5% spandex',

        7993 => 'Trendy A-shaped shirt dress with short sleeves and beautiful, wide collar. Visible metal zip on back that gives the dress a hot, tough-looking twist.

Styling tips: Style with leggings, e.g. style Luxx. Wear the dress as a shirt over a pair of jeans.

Fit: A-shaped shirt dress.
Length: Sizes: XS: 84 cm/ S: 85,5cm / M: 87cm /L: 88,5cm

Fabric: 97% cotton 3% spandex.',

        8276 => 'Cool sweat jacket or dress - wear it just as you like. Closes with oversize buttons and comes with a belt in the same fabric as the jacket. The jacket has a stylish and capacious silhouette with many great details. Super cool sleeves and many great cuts.

Styling tip: This design gives many opportunities. Use it as a dress, a cardigan or jacket with or without belt and wear it over trousers, dresses or leggings.

Fabric: 100% cotton.

Length: Sizes: XS =88 cm S= 89.5 cm M= 91 cm L=92.5 cm.',

        8275 => 'Raw sweat jacket/cardigan with many details. Super chic collar, high rib cuffs, decorative buttons along the sleeve and attached pockets.

Closes with tie-strings below the chest.

Styling tip: This design gives many opportunities. It can be worn as both a jacket and a cardigan. Style it with a dress or wear it with harem pants style Kiki.

Fabric: 100% cotton.

Length: Sizes: XS = 65 cm S= 66.5 cm M= 68 cm L=69.5 cm.',

        8270 => 'Great sweat dress with a lovely oversize roll neck. Difference in length between front and back. Tie cord along top and bottom.

Styling tip: Can be worn with a belt around the waist - style Rollercoaster.

Fabric: 100% cotton.

Length: Sizes: XS = 96 cm S=97.5 cm M=99 cm L=100.5 cm.',

        8277 => 'Deep V-neck top or dress - It is up to you. Capacious fit with decorative imitation diamonds in the front and along the back. Nice big pockets and tie-strings along the bottom.

Fabric: 100% cotton.

Length: Sizes: XS =96 cm S=97.5 cm M=99 cm L= 100.5 cm.',

        8285 => 'Chic shirt dress with short sleeves. The waist band is placed high up to visually create a slimmer waist.

Great wrinkly neck opening. The contrasting effect of the black buttons adds a nice extra detail. Difference in length between front and back piece.

Styling tip: The shirt dress is perfect for many occasions and can be worn with or without belt. The belt (style Luxx) fits very well with the shirt and emphasizes the contrasting effect of the black buttons.

Fabric: 100 % cotton, chambray.

Colour: light blue denim colour.

Length in sizes: xs =90 cm s=91.5 cm m=93 cm l=94,5 cm.',

        8286 => 'Stylish and feminine tunic with �-sleeves and with many details. Great neck opening with oversize button. Wrinkly effect on chest and back.

The design silhouette is slightly A-shaped which visually creates a slimming effect.

Styling tip: Wear the tunic as a mini dress over leggings (style Luxx) or barelegged. The tunic is also chic as a shirt blouse over a pair of trousers.

Fabric: 100 % cotton, chambrey.

Colour: Light blue denim.

Length in sizes: xs =88 cm s= 89.5cm m=91 cm l=92.5 cm.',
        8281 => 'Beautiful strap dress in jersey quality with a handmade decorative panel of dim-surfaced golden-coloured pearls and black sequins on the front piece. Difference in length between front and back.

Stylish details on back with buttons and open cut to bare skin.

Styling tip: The dress gives many opportunities for styling and can be worn on both weekdays and for parties.

The layered look: Wear the dress over a pair of leggings (style Luxx) or harem pants. Add a belt around the hip and possibly a cardigan (style United).

Party: Style the dress with a pair of chic net or lace stockings and a pair of high-heeled shoes.

Weekdays: Wear the dress as a long top over a pair of jeans and possibly with a top or shirt underneath the dress.

Fabric: 100 % viscose.

Length in sizes: xs =82 cm s= 83.5cm m=85 cm l= 86.5cm.
',
        8279 => 'Stylish top with orange, purple and black sequins. The dress has a flexible top piece and therefore a great fit.

The top can be worn as a tube top without straps, a halterneck top with straps tied around the neck or with straps over the shoulder making possible bra straps invisible.

Difference in length between front and back piece.

Styling tip: Wear the top over a pair of leggings (style luxx) or harems pants (style Kiki). Can be styled over a tank top, a shirt or underneath a cardigan (style United).

Fabric: 100 % viscose.

Length along side hem in sizes: xs = 56cm s=57.5 cm m=59 cm l= 60.5cm.',
        8290 => 'Stylish and trendy harems pants with oversize metal studs alongside the pockets. Wrinkly detail along the lower leg making it possible to style the pants leg as you wish. (See pictures).

Fabric: 100 % viscose.',
        8288 => 'Sweet A-shaped shirt dress with short sleeves and elastic cords on the end of the sleeve. Fabric-covered button along the sleeve and a satin panel on the neck part and on the sleeve. V-neck with wrinkly effect along the hem.

Styling tip: Wear the shirt dress as a dress over leggings (style Luxx) or as a tunic over a pair of jeans.

Combine the dress with a short blazer jacket (style Cloe).

Fabric: 100 % cotton.

Colours: Black or coral.

Length in sizes: xs = 100cm s= 102cm m= 103cm l=105 cm.',
        8300 => 'Sweet A-shaped shirt dress with short sleeves and elastic cords on the end of the sleeve. Fabric-covered button along the sleeve and a satin panel on the neck part and on the sleeve. V-neck with wrinkly effect along the hem.

Styling tip: Wear the shirt dress as a dress over leggings (style Luxx) or as a tunic over a pair of jeans.

Combine the dress with a short blazer jacket (style Cloe).

Fabric: 100 % cotton.

Colours: Black or coral.

Length in sizes: xs = 100cm s= 102cm m= 103cm l=105 cm.',
        8301 => 'Shirt dress with short sleeves and cool details. Closes with fabric-covered button on the front. Great neck opening and big pockets. Difference in length between front and back piece.

Elastic detail on the sleeve and on back. A smock panel gives a stylish and flexible fit.

Styling tip: Wear the shirt dress over leggings (style Luxx). Combine the dress with a short blazer jacket (style Cloe).

Fabric: 100 % cotton.

Colours: Black or coral.

Length: xs =95 cm s= 96.5cm m= 98cm l= 99.5cm.',
        8289 => 'Shirt dress with short sleeves and cool details. Closes with fabric-covered button on the front. Great neck opening and big pockets. Difference in length between front and back piece.

Elastic detail on the sleeve and on back. A smock panel gives a stylish and flexible fit.

Styling tip: Wear the shirt dress over leggings (style Luxx). Combine the dress with a short blazer jacket (style Cloe).

Fabric: 100 % cotton.

Colours: Black or coral.

Length: xs =95 cm s= 96.5cm m= 98cm l= 99.5cm.',

        8278 => 'Chic and trendy tube dress with cool belt. The zip in golden metal gives the dress a raw look. Wrinkly effect camouflages and gives a slimming effect. Great shiny and flexible quality.

Styling tip: The dress gives many opportunities for styling. It is super cool over a pair of leggings (style Luxx), harem pants (style Kiki) and can be styled with a belt (style Rollercoaster). Try the dress with a pair of jeans and you have a nice party top. The dress can also be styled over a shirt blouse. This way you have a great dress for weekdays and parties. Combine the dress with a cardigan (style United) and you create a yet another look.

Fabric: Polyester/elasthane.

Length along side hem in sizes: xs = 73cm s=74.5 cm m= 76cm l= 77.5cm.',

        8280 => 'Short-sleeved grey melange dress in jersey quality with nice silver glitter on the fabric surface. A decorative sequin butterfly on the chest. Closes with buttons on back.

Styling tip: Wear the dress over a pair of leggings or over a pair of trousers.

Fabric: cotton/polyester.

Length in sizes: xs = 92cm s= 93.5cm m= 95cm l=96.5cm.',

        8282 => 'Rock\'chick grey melange dress in jersey quality with nice silver glitter on the fabric surface. Wrinkly detail along the sleeve and on the chest. Asymmetrical bottom piece. Button details along side piece.

Styling tip: The dress is super cool with a belt around the waist. Also wear the dress as a top over a pair of leggings or over a pair of trousers.

Fabric: cotton/polyester.

Length in sizes: xs =72 cm s=73.5 cm m= 74cm l= 76.5cm.',

        8283 => 'Short cardigan bolero in great jersey quality with � sleeves and nice neck opening. Many details such as wrinkly effect along the sleeve and wide cuffs. Closes with oversize buttons in the front.

Styling tip: The cardigan bolero completes every wardrobe and is a must-have for every woman. It can be styled for any occasion and all looks. Wear it over a party dress or in weekdays over a shirt dress or a pair of jeans.

Fabric: 100 % viscose.

Length in sizes: xs = 42cm s= 43.5cm m= 45cm l=46.5cm.',

        8284 => 'Super chic and short �-sleeved blazer jacket designed with inspiration from the men\'s wardrobe. Fantastic neck cut. Closes with buttons in contrasting colours. Button detail on back.

Styling tip: The blazer jacket is a must-have item for the spring. It completes the layered look. Mix the blazer jacket with a tee, a dress, leggings and a pair of pumps to create a cool and urban look.

Fabric: 100 % cotton, chambray.

Colour: light blue denim colour.

Length in sizes: xs =55cm s= 56.5cm m=58 cm l=59,5 cm.',

        8299 => 'Super chic dress with belt. Deep and stylish neck cut with collar. Closes with zip in the front. Straps on the sleeves buttoned to the shoulder. Big pockets.

Styling tip: Wear the dress over leggings (style Luxx) with a pair of high-heeled shoes. A posh and elegant look.

Fabric: 100 % cotton.

Length in sizes: xs = 100cm s=102 cm m=103 cm l= 105cm.',

        8382 => 'A great piece of outer garment with slimming silhouette and many details.
Big fabric-covered buttons in front.
Fabric-covered buttons along back.
Stylish spacious and creased hood.
Pockets.
Lovely sleeves.

Fit: Small A-shape. Beautiful and slimming cut and fit.
Length�cm :�XS:�88/�S:�89,5 /�M:�91 /�L:92,5
�
Compostion: 100 % cotton
Dryclean',

        8384 => 'Chic and stylish knitwear with deep V-neck. Beautiful sleeves with long cuffs. Difference in length between front and back piece. Decorative buttons along back from top to bottom.

Styling tips: Wear a small top underneath the knitted blouse. Can be styled with jeans and leggings or with a petticoat, which gives a great effect as the blouse length varies between front and back.

Fit: Loose fit.
Length�cm :�XS:�75/�S:�76,5 /�M:�78 /�L:79,5

Composition: 60% viscose 40% cotton',

        8383 => 'Terrifically trendy knitted cardigan with big, oversized collar, short sleeves and big pockets. The cardigan closes with silver metal press studs. Small difference in length between front and back piece.

Extra attention has been given to details in the knitwear. Nice rib around the shoulders and on the back. Decorative hole-patterns along armhole.

Styling tips: The cardigan has the perfect tough look for fall 2009. It is a bit oversized and is super chic with leggings or a tight pair of jeans.
You can also wear the cardigan as a piece of outer garment. Style it with a great sweater underneath, add a pair of long leather gloves and be super sophisticated.

Fit: Loose fit.
Length�cm :�XS:�86/ S: 87,5 /�M:�89 / L:90,5
�
Composition: 60% viscose 40% cotton',

        8390 => 'Short jacket with �-sleeves perfecting any outfit. The jacket is full of attitude and lavish details.
Look-alike leather quality with wrinkly effect. Closes with big fabric-covered buttons. Standing collar and entire jacket sown with panels. To perfect the look we have added a quilted effect.

Styling tips: A cool look is obtained with our style Zone by wearing a dress or spacious shirt underneath the leather jacket. Can replace a blazer jacket, cardigan and can even be worn as outer garment in mild weather.

Fit: Hot form-fitting fit. Incredibly stylish and slimming.
Length�cm :�XS:�43/ S: 44,5 /�M:�46 / L:47,5
�
Composition:�Look-alike-Leather// 100% polyester',

        8406 => 'Stylish and sophisticated dress in super cool quality -�look-alike leather with wrinkly effect. Sleeves with puff and nice width. Zip detail on back giving a tough look.
Styling tips: The dress is a must-have item perfect for this fall. Style it with a tube scarf style Bella and a pair of leggings.
Fit: Incredibly chic fit. Perfect for a curvy body.
Length�cm :�XS:�96/ S: 97,5 /�M:�99 / L:100,5
�
Composition:�Look-alike-leather // 100% polyester',

        8415 => 'Beautiful and feminine high-waist skirt with elegant details. Wrinkly decorative panels on the front waistpiece to camouflage the stomach. Tough and sexy zip detail on the back with the opportunity to open a slit. The back waist piece is decorated with cool wrinkly details. The skirt is lined with satin.
The fabric is flexible and takes shape after the body. Fabric surface has shiny effect at surface.
Styling tips: Wear the skirt with leggings or over a pair of hot stockings. Can be styled with either a tight T-shirt or shirt tucked down the skirt or hanging loose.
Fit: Tight-fitting and high-waisted skirt. Slimming cut.
Length�cm :�XS:�55/ S:�56 /�M:�57 / L:58
�
Composition: 97% cotton 3% elastan',

        8416 => 'Elegant and sophisticated dress with pockets. Zip detail in front and �-sleeves with smock. Fabulous open neck cut in front. Open neck cut with button on back.
The fabric is flexible and the surface has a shiny effect at surface.
Styling tips: Style the dress with leggings or a pair of hot stockings. The dress is great with both boots or shoes.
Fit: Small A-shape, slimming cut.
Length�cm :�XS:�92/ S: 93,5 /�M: 95 /L:96,5
�
Composition: 97% cotton 3% elastan',

        8414 => 'Super cool and hot jacket with �-sleeves, belt and pockets. The jacket can be worn both indoor and outdoor in mild weather.

The jacket has a unique design with many details. Shoulder straps and adjustable sleeves to adjust the length. Quilted detail on chest and along back. Wrinkly detail at waist to camouflage around hip and waist. Fabulous fabric-covered buttons.
The fabric is flexible with a shiny effect at surface.

Fit: Remarkably great fit. Perfect fit across the chest and over waist and hip. Loose sleeves.
Length�cm :�XS:�68/ S: 69,5 /�M:�71 /L:72,5
�
Composition: 97% cotton, 3% elastan',

    8413 => 'Super cool long, dark grey satin top. Assymmetrical sleeves give the top a stylish look. The printed motif of wild horses contrasts the grey colour perfectly. Small slit in side hem.

Styling tips: The top can either be worn as a long top or as a dress. Can be styled with leggings or a tight pair of jeans.

Fit: Loose fit.
Length�cm :�XS:�90/ S: 91,5 /�M:�93 /L: 94,5
�
Compostion: 97% polyester,  3% elastan',

    8385 => 'Super elegant dark grey satin top with assymmetrical neck cut and wrinkles. Stylish sleeves. Zip detail and wrinkly effect on back adding tough look.

Styling tips: The top can either be worn as a long top or as a dress. Can be styled with leggings or a tight pair of jeans.

Fit: Loose fit.
Length�cm :�XS:�94/ S: 95,5 /�M:�97 /L: 98,5
�
Compostion: 97% polyester, 3% elastan',

    8400 => 'Tunic / shirt dress with �-sleeves and flexible, soft smock around armholes. Great V-neck cut with fabulous fabric-covered buttons. Small collar with wrinkly effect.

Styling tips: Wear the tunic over a pair of leggings or jeans, or wear it as a dress. Also very nice with a belt in waist.

Colour: The dress is available in both strong purple and black.

Fit: Small A-shape, Loose fit.
Length�cm :�XS:�93 / S:�94,5 �/�M:�96,5��/ L: 97,5
�
Compostion: Light and exquisite 100% cotton',

    8397 => 'Wrap-around dress with belt and beautiful details. Small fabric-covered buttons along shoulders and lovely embroidered sleeve rims. Wrinkly effect on back.

Styling tips: Style the dress over a petticoat, leggings or jeans. Or as a dress over a pair of hot stockings.

Colour: The dress is available in both strong purple and black.

Fit: Loose fit.
Length�cm :�XS:�100 / S:�101,5 �/�M:�103 �/L:104,5
�
Compostion: Light and exquisite 100% cotton',

    8398 => 'Wrap-around dress with belt and beautiful details. Small fabric-covered buttons along shoulders and lovely embroidered sleeve rims. Wrinkly effect on back.

Styling tips: Style the dress over a petticoat, leggings or jeans. Or as a dress over a pair of hot stockings.

Colour: The dress is available in both strong purple and black.

Fit: Loose fit.
Length�cm :�XS:�100 / S:�101,5 �/�M:�103 �/L:104,5
�
Compostion: Light and exquisite 100% cotton',

    8401 => 'Long shirt / shirt dress with big buttons. Open and round neck cut with wrinkly effect. Super nice puff sleeves with elatic band at armhole.
Bias binding at shirt bottom and wrinkly effect along the rim. Elastic detail on back giving a perfect fit.

Styling tips: Style the dress over a pair of leggings or jeans.

Fit: Casual, loose fit.
Length�cm:�XS:�93 / S:�94,5 �/�M:�96��/L:97,5
�
Compostion: Light and exquisite 100% cotton',

    8402 => 'Long shirt / shirt dress with big buttons. Open and round neck cut with wrinkly effect. Super nice puff sleeves with elatic band at armhole.
Bias binding at shirt bottom and wrinkly effect along the rim. Elastic detail on back giving a perfect fit.

Styling tips: Style the dress over a pair of leggings or jeans.

Fit: Casual, loose fit.
Length�cm:�XS:�93 / S:�94,5 �/�M:�96��/L:97,5
�
Compostion: Light and exquisite 100% cotton',

    8399 => 'Stylish tunic dress with many details. Lovely pleats and nice laces along sleeves. Lace detail in front and back. Closes in front with small fabric-covered buttons. Cross-stitch embroideries along dress bottom.

Styling tips: Wear the tunic over a pair of leggings and boots with a tough look. Style with a short jacket (style Zone) and create a fashionable layered look.

Fit: casual and loose�
Length�cm :�XS:�94 / S:�95,5�/�M:�97�/ L: 98,5
�
Compostion: Light and exquisite 100% cotton',

    8395 => 'The design is edgy and super cool for this shirt with open neck cut and wrinkly effect along neck.
�-sleeves with small puffs. Asymmetrical bottom. Zip detail on back adding tough look. Great exclusive soft cotton quality giving a fabulous look with shiny effect on surface.

Styling tips: The shirt is super cool over a pair of leggings or jeans. Can be styled with a small jacket.

Fit: Loose fit.
Length at center front cm :�XS:�82 / S:�83,5 �/�M:�85��/L: 86,5
�
Composition:�100% Delicate cotton  �
Hand wash �',

    8394 => 'Funky dress with belt. At the same time giving a tough, but also feminine look. Asymmetrical shoulder straps. One strap is detachable completing the assymmetrical look. Elastic top part keeps the dress in place. Frills on one shoulder strap and along the dress.
Exquisite and soft cotton quality giving a fabulous look with shiny effect on surface.

Styling tips: Be feminine and style the dress with a pair of hot stockings and high-heeled shoes. Be rock\'chick with a pair of cool leggings or jeans with a wide belt around the waist. On working days the dress can be worn over a small shirt or T-shirt. Incredibly usable dress that can spark up any occasion.

Fit: Remarkably great fit. Loose all the right places.
Length from top of shoulder cm: XS: 87 / S: 88,5 / M: 90 /L: 91,5

Composition:�100% Delicate cotton
Hand wash',

    8396 => 'Must-have shirtdress for this season. Can be worn on working days and for parties. Has many super nice details, beautiful cuts and pockets. Stylish and open neck cut. Big fabric-covered buttons. Beautiful sleeves with wrinkly effect for a cool look that also camouflages the arms.
Fabulous and soft cotton quality giving a great look with a shiny effect on surface.

Styling tips: The shirt can be worn as a shirt dress. Style the shirt with a small jacket i.e. style Zone.

Fit: Loose fit.
Length�front cm :�XS:�84 / S:�85,5 �/�M:�87��/L: 88,5
�
Composition:�100% Delicate cotton �
Hand wash',

    8393 => 'Must-have halterneck top. A basic top that is indispensable. Beautiful round neck cut and bare back.
Great cotton quality that keeps the shape.

Styling tips: A halterneck top can be worn with everything. For example, you can wear it under a top with open neck making only the strap around the neck visible. Gives an interesting and individual look.

Fit: Tight-fitting- Extra length.
Length cm, along side seam under arm: XS:59 / S:60 / M:61 / L:62',

    8417 => 'The desing is cool and edgy for this jersey dress with many details. Stylish cuts fitting the curvy body perfectly.
Deep V-cut with wrinkly effect. Becoming draped details around the hip. Zip detail on back adding a tough look. Loose elastic around the waist.

Composition:� 100% rayon
�
Fit: slimming and loose fit.
Length�cm :�XS:�94 / S:�95,5 /�M:�97 /L: 98,5',

    8391 => 'Style Birdy is a must-have. Casual, comfortable, but still chic. The trousers can be tied with cords around the waist. Many details.

The trousers are a mix between two different qualities. Great jersey decorated with beautiful, woven cotton at the waist giving a great play between the qualities. The elastic effect along the top piece gives a super fit at the waist.
Decorative tie-strings at the ancle. The bottom width can be adjusted with press stud buttons.

Styling tips: The trousers have a slimming silhouette. Great for both weekdays and parties. Only your own imagination limits the use of style Birdy. Style the trousers with a party top, a pair of heels and maybe a cool jacket or blazer and you have a party outfit. Or tone down the look with a t-shirt and you have the outfit for a cozy and relaxing movie night.

Fit: Loose and casual fit.
Inside leg (measured on the inside of the leg, from the crotch down along the leg):
�XS:�74 / S:�74/�M:�74 /L: 74.

Composition: 95% viscose, 5% lycra.',

    8410 => 'This autumn\'s must-have item is carmakoma\'s new tube scarf. Can be wrapped around the neck as a normal scarf or leaved hanging so it drapes around the neck. A third opportunity is to style it over the hair as shown on the picture.

Styling tips: The scarf can be styled with anything and gives you a sophisticated look. Use it both indoors and outdoors and on weekdays or for parties.
The scarf can also be worn as a poncho over the arms if you feel bare. The circle shape gives endless opportunities for styling.

Colour: Dark and sophisticated grey melange.

Composition: 100% rayon.

One size: Length 67 cm.',

    8392 => 'Triangular, fabulous scarf with cross-stitched embroideries of birds. Big tassels along the seam and the perfect peach colour to lighten up any outfit.
The colours of the embroidery and scarf are tone in tone.

Colour: Really beautiful coral/peach.

Styling tips: Wear this scarf both indoors and outdoors. The colour is perfect for this season - so wear it to spark up any outfit.

Composition:�100% cotton.',

    8412 => 'Edgy and cool 2-in-1 jersey dress. Can be worn as one or separately.
Outer dress: Super nice dress with detailed shoulder straps. One shoulder strap is twisted giving the front piece a nice drape that ends in a lavish waterfall neck cut.
Beautiful back cut with pearl and sequin decorated panel. The dress is designed with cuts giving the dress a stylish draped and slimming look.

Inner dress: Great open neck cut. Slim fit. Short sleeves with wrinkly effect. Can also be worn separately as a long top.

Wear the two dresses together and you have the perfect dress - both hot and sexy, but also super sophisticated and elegant.

Styling tips: Try to style the dress with a belt around the hip and leggings or jeans underneath. Then you suddenly have a smart top. The outer dress can also be styled with tops in contrasting colours if you prefer the coloured look. Only your own imagination sets limits for the use of this 2-in-1 dress.

Fit: Outer dress// Draped and loose fit.
Inner dress// tight-fitting.
Front length in cm: XS:�94 / S:�95,5 �/�M:�97��/L: 98,5.

Composition:�100% rayon.',

    8408 => 'Super cool sweatshirt dress with hood. The quality is carmakoma\'s very popular cotton sweat quality loved by our customers.
Many details - fabulous, "heavy" double-layered hood and deep cut. Stylish sleeves with tight-fitting cuffs and loose around the upper arms.
Tie-strings in the bottom of the dress give options to vary the tightness of the fit.

Styling tips: The perfect and indispensable sweatshirt dress. Can be worn as a dress with a pair of cool stockings or leggings and boots or pumps. Can also be worn as a casual top with a pair of tight jeans.

Colour: Nice light grey melange.

Fit: Casual and loose fit.
Length in cm :�XS:�93/ S:�94,5��/�M:�96��/L: 97,5.
�
Composition:� 95% Cotton 5% spandex.',

    8409 => 'Cardigan/bolero with lots of great details in original design. Super cool and a must-have to complete the perfect wardrobe.

The quality is carmakoma\'s very popular cotton sweat quality loved by our customers.

�-sleeves with wrinkly effect and long cuffs. Tie-strings to keep the top closed, although it is also super trendy when open. Fabric covered buttons in front from neck and down.
Wrinkly details on back. Back length is longer than front length.

Styling tips: This cardigan/bolero is a statement in itself. Can be worn to spark up even the most simple outfit.
Style it with carmakoma\'s tube scarf "Bella" and you have an edgy and sophisticated look.
This bolero can with great success be combined with dresses as it has a perfect length and cut in the front.

Colour: Nice light grey melange.

Fit: Casual with a close and super attractive fit without feeling tight.
Length in cm: XS:�59/ S:�60,5��/�M:�62��/L: 63,5.

Composition: 95% cotton 5% spandex.',

    8411 => 'Sophisticated, edgy and super smart jersey dress that accentuates the best part of a curvy woman\'s body.
Long slimming sleeves, great deep neck cut with pleat. Draped details on the hip. Tight-fitting skirt underneath. Tie-strings in the neck as an extra cute detail.

Styling tips: Style the dress with a small, smart jacket and a belt around the waist. A pair of high heels gives a chic look. On weekdays the dress is nice with a pair of thick stockings or leggings and a pair of tough-looking boots.
If you want the tough look, combine the dress with a pair of worn-out jeans and with a tough-looking jacket like carmakoma\'s style Zone. Wear a scarf around the neck.
�
Fit: Slimming silhouette with a close and super attractive fit without feeling tight.
Length in cm :�XS:�110/ S:�111,5 /�M:�113 /L: 114,5.

Composition: 100% rayon.',

    8417 => 'Smart jersey dress with many details and short sleeves.
The dress is decorated with cuts giving a slimming and also very exciting look.

Beautiful deep V-neck cut with wrinkly effect. Draped and becoming details on the hip. The tough-looking zip detail on the back gives the dress its original carmakoma look.

Styling tips: The dress is hip and super smart as it is, but is also cool over a pair of jeans with tough-looking boots. A straight jacket and a scarf around the neck adds something to both looks.

Colour: Dark and sophisticated grey melange.

Fit: Slimming silhouette, but still comfortable and casual.

Composition:�100% rayon.',

    8418 => 'Great cardigan with slimming sleeves and beautiful neck cut with wrinkly effect. In the front the cardigan is decorated with a grey band. Closes with large press stud buttons. Wrinkly effect along closing. Difference in length between front and back.

Styling tips: Beautiful open neck cut. Can be worn buttoned up as a roll neck. The neck can also be left open.
Use your imagination with this cardigan. Close the press stud buttons asymmetrically (by missing one button) and you will add extra creases and wrinkly effect to the front piece.

Colour: Dark and sophisticated grey melange.

Composition:�100% rayon.',

    8405 => 'Style Madonna is carmakoma\'s must-have rock\'chick tulle skirt with matching belt.
The skirt has perfect volume without being too big. The lovely lace bands make a good contrast to the tough-looking detachable belt.

Styling tips: Style the skirt with a tight-fitting top and with a tough-looking jacket.
The tulle skirt is perfect for parties. Style with a pair of high stillettos, long necklaces and three big metal bracelets on the wrist.

Colour: Black and strong purple.

Fit: Elastic waist, spacious fit with detachable belt.
Length in cm:�XS:�53/ S:�54 �/�M:�55 �/L: 56.

Composition: 100% Nylon (Tulle).',

    8403 => 'Style Madonna is carmakoma\'s must-have rock\'chick tulle skirt with matching belt.
The skirt has perfect volume without being too big. The lovely lace bands make a good contrast to the tough-looking detachable belt.

Styling tips: Style the skirt with a tight-fitting top and with a tough-looking jacket.
The tulle skirt is perfect for parties. Style with a pair of high stillettos, long necklaces and three big metal bracelets on the wrist.

Colour: Black and strong purple.

Fit: Elastic waist, spacious fit with detachable belt.
Length in cm:�XS:�53/ S:�54 �/�M:�55 �/L: 56.

Composition: 100% Nylon (Tulle).',
    8504 => 'Description & styling tips by Weiss & Lykke:

Party top with super sexy open back and twisted detail. Cascading neck cut. Delivered with matching belt. Super great cotton quality.

For a less bare look it can be styled with a halterneck top or a t-shirt. For variation combine the top with a vest and a pair of tight pants.

For stylish combinations that go perfectly with this top, see carmakoma styles:
Pants Roberto, Halterneck top Daiquiries, Belt Rollercoaster, Scarf Birdyscarf.

Fit: Loose fit.

Length in cm: XS:71 / S:72,5 / M:74 / L:75,5.

Fabric: 100% cotton.

Colours: Black.',
    8510 => 'Beautiful champagne coloured dress with graphic print across shoulder and chest. Print colours are coral, yellow, black and white. The tucked waistline completes the design. The dress has v-cut and short sleeves.

The dress can be used for any occasion. The beautiful print makes it festive and eye-catching. Perfect for parties. Style the dress with oversize jewelry. Wear large earrings and decorate the arm with big bracelets. Finish off with your favourite stilettos. If you don\'t prefer bare legs, then style the dress with nice stockings or leggings.
You can also dress it down for weekdays by wearing a pair of tight pants underneath.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Cardigan Jaume, Leggings Luxx, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5.

Fabric: Satin - 100% Polyester

Colours: Champagne colours with print.',

    8508 => 'Rock\'chick, funky dress, yet still super feminine. Metal studs decorate the chest. Frilled sleeves, asymmetrical bottom and zip details on back.
This seasons must-have dress for any occasion. For a party look combine the dress with oversized silver jewelry and a pair of nice stockings or leggings. The dress is super cool with a belt around the waist. Complete the look with your favourite stilettos.
For weekdays style the dress with a pair of tight pants and a light jacket with a scarf around the neck.
For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Jacket Snoop, Leggings Luxx, Belt Rollercoaster.

Fit: Perfect tight fit across the chest. Lower part of the dress is two-layered. The inside part is tight-fitting with a loose outer part. The dress has a slimming effect and is perfect for all body structures.

Length in cm: XS:94 / S:95,5 / M:97 / L:98,5.

Fabric: 100% Viscose

Colours: Black with silver metal trimming.',

    8509 => '"The vest is back" and is a must-have item this season. carmakoma\'s vest is fabulous with large beautiful and shiny pearls. The back is quilted. Closes with tie-strings in front.

The vest can be combined with almost everything. Wear it over a top or dress. The lavish pearls give an elegant look and make it suitable for parties.

For stylish combinations that go perfectly with this vest, see carmakoma styles:
Pants Roberto, T-shirt dress Novo, Dress Diagonal, Dress Picasso.

Fit: Beautiful tight-fitting vest.

Length in cm: XS:44/ S:45,5 /M:47/ L:48,5.

Fabric: 100% Viscose

Colours: Black with black pearls.',

    8527 => 'Fantastic strap dress. Can be used as a lovely summer dress or as a top over a pair of jeans.
The dress has double-etched closing with rustic wooden buttons. Wrinkly detail in front around waist and hips.
The shoulder straps are flexible and can be unbuttoned in the back and tied around the neck.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Vest: Paris, Leggings Luxx, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:83,5 / S:85 / M:86,5 / L:88.

Fabric: 100% Polyester

Colours: Available in coral & black.',
    8497 => 'Fantastic strap dress. Can be used as a lovely summer dress or as a top over a pair of jeans.
The dress has double-etched closing with rustic wooden buttons. Wrinkly detail in front around waist and hips.
The shoulder straps are flexible and can be unbuttoned in the back and tied around the neck.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Vest: Paris, Leggings Luxx, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:83,5 / S:85 / M:86,5 / L:88.

Fabric: 100% Polyester

Colours: Available in coral & black.',

    8528 => 'Fantastic dress with asymmetrical frills. Wide shoulder straps in contrasting colour (black). Cute detail on back with tie-strings and small wooden pearls. Difference in length between front and back.
The dress can be combined with a fancy and feminine vest (Style Paris). For parties you can style it with large, beautiful jewelry.
For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Vest Paris, Leggings Luxx, Belt Rollercoaster.

Fit: Fabulous fit with slimming effect.
Length in cm: XS:82,5 / S:84 / M:85,5 / L:87.
Fabric: 100% Polyester
Colours: Available in coral & black.',
    8498 => 'Fantastic dress with asymmetrical frills. Wide shoulder straps in contrasting colour (black). Cute detail on back with tie-strings and small wooden pearls. Difference in length between front and back.
The dress can be combined with a fancy and feminine vest (Style Paris). For parties you can style it with large, beautiful jewelry.
For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Vest Paris, Leggings Luxx, Belt Rollercoaster.

Fit: Fabulous fit with slimming effect.
Length in cm: XS:82,5 / S:84 / M:85,5 / L:87.
Fabric: 100% Polyester
Colours: Available in coral & black.',

    8496 => 'Fabulous and trendy shirt/jacket with shoulder decoration of stones and metal trimming. Thin shoulder padding. Small collar and tie-strings in front. �-sleeves and small hidden pocket in the side seam.

For a super hot look combine the jacket with a pair of jeans and a small top underneath. A belt around the waist toughens up the look.
The beautiful shoulder decoration is lavish in itself, so further accessories are not necessary. The jacket can also be worn as a small jacket over a dress.

For stylish combinations that go perfectly with this jacket, see carmakoma styles:
Pants Roberto, Halterneck top Daquiries, Leggings Luxx, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:75 / S:76,5 / M:78 / L:79,5.

Fabric: 100% Polyester

Colours: Black with metal trimming.',
    8511 => 'T-shirt/t-shirt dress. This style can be used a top over a pair of jeans or as a T-shirt dress with bare legs or leggings.
Graphic print across shoulder and chest in black with a silver glitter outline.
Small slit at bottom of side seam.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Jacket Snoop, Vest Paris, Leggings Luxx, Belt Rollercoaster, Scarf birdyscarf.

Fit: Loose fit.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5.

Fabric: 95% viscose, 5% lycra.

Colours: Off-white.',
    8519 => 'Description & styling tips by Weiss & Lykke:

Super fantastic and hot dress in the most beautiful strong blue colour. Deep V-neck in front and v-cut in back. Lovely decoration seams in front and back. Difference in length between front and back.
The dress can be combined with pants to be worn as a top.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Jacket snoop, Pants Roberto, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:92 / S:93,5 / M:95 / L:96,5.

Fabric: 100% Polyester

Colours: Strong blue.',

    8505 => 'Description & styling tips by Weiss & Lykke:

Feminine crinklecotton dress with many details. Deep neck cut with tie-strings and small wooden pearls. 3/4-sleeves with beautiful detail.
Difference in length between front and back piece. Lovely crocheted lace panel on back top piece and nice crocheted cotton ribbon along the seam.

The dress is ideal for summer when combined with a petticoat and bare legs. Or wear it as a top over a pair of pants. Perfect for all occasions and can be styled for anything. For the party look style it up with jewelry and stilettos. For weekdays wear it with flat shoes, pants and possibly a scarf around the neck.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Petticoat Mutaner, Vest Paris, Pants Roberto.

Fit: Loose fit.

Length in cm: XS:95 / S:96,5 / M:98 / L:99,5.

Fabric: 100% Viscose

Colours: Off-white.',
    
    8505 => 'Description & styling tips by Weiss & Lykke:

Feminine crinklecotton dress with many details. Deep neck cut with tie-strings and small wooden pearls. 3/4-sleeves with beautiful detail.
Difference in length between front and back piece. Lovely crocheted lace panel on back top piece and nice crocheted cotton ribbon along the seam.

The dress is ideal for summer when combined with a petticoat and bare legs. Or wear it as a top over a pair of pants. Perfect for all occasions and can be styled for anything. For the party look style it up with jewelry and stilettos. For weekdays wear it with flat shoes, pants and possibly a scarf around the neck.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Petticoat Mutaner, Vest Paris, Pants Roberto.

Fit: Loose fit.

Length in cm: XS:95 / S:96,5 / M:98 / L:99,5.

Fabric: 100% Viscose

Colours: Off-white.',
    
    8630 => 'Description & styling tips by Weiss & Lykke:

Super feminine summer dress/top with belt and matching petticoat. 100% cotton.
Beautiful crocheted hem along the sleeve and neck cut with wrinkly effect.
Petticoat is not attached so it can be worn underneath other clothes. Adjustable straps on petticoat.

Wear the dress over a pair of pants as a top to toughen up the look. Style it with carmakoma\'s belt Rollercoaster.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5.

Fabric: 100% Viscose

Colours: Black.',

    8513 => 'Description & styling tips by Weiss & Lykke:

"Boyfriends shirt" - cool and stylish. This pinstriped shirt in blue/white colour combination is delivered with matching belt, but is also super hot as a large loose-hanging shirt.
Sexy, deep neck cut with collar, chest pocket and side seam pockets. White cuffs and front panel are beautifully contrasting the stripes. Difference in length between front and back.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Jacket Snoop, Cardigan Jaume, Pants Roberto, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:92 / S:93,5 / M:95 / L:96,5.

Fabric: 100% cotton.

Colours: Blue/white colour combination.',
    8512 => 'Feminine shirt dress in fabulous blue- and whitestribed cotton quality with many details. White contrasting hems, v-neck cut in front and back and tie-strings on back.
Creased detail on front and back. Side seam pockets. Great detail with small, closely placed buttons on front and back. Bias binding on bottom of dress gives cool wrinkly effect.

Style your shirt dress with leggings or a pair of tight pants, a vest and a scarf around the neck. A super hot outfit for both weekdays and parties. The shirt dress is also terrific with a belt around the waist.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Jacket Snoop, Vest Paris, Pants Roberto, Belt Rollercoaster, Scarf Birdyscarf.

Fit: Sexy tight fit across the chest. The wrinkly effects in front and back give a great camouflage effect across waist and hips.

Length in cm: XS:93 / S:94,5 / M:96 / L:97,5.

Fabric:100% Cotton.

Colours: Blue- and whitestriped colour combination with white contrasting hems and white buttons. ',
    8501 => 'Description & styling tips by Weiss & Lykke:

Beautiful, elegant dress with round neck, short sleeves and large attached pockets. Delivered with a belt of same material as the dress. Nice decorative seams for a visually slimming look.

The dress is chic over a pair of leggings, tight pants or a pair of hot stockings. Can easily be styled for both parties and weekdays. The dress design is very simple so it goes perfect with large accessories.
You can also try a leather belt around the waist for variation.

Super great quality that lasts wash after wash.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Vest Paris, Pants Roberto, Leggings Luxx, Belt Rollercoaster, Scarf Birdyscarf.

Fit: Loose, perfect fit.

Length in cm: XS:95 / S:96,5 / M:98 / L:99,5.

Fabric: 100% Cotton.

Colours: Black.',
    8502 => 'Description & styling tips by Weiss & Lykke:

Beautiful, elegant dress. 3/4-sleeves with a flexible elastic piece in bottom. Hot deep neck cut. "Wrap-around-look". Side seam pockets and wrinkly effect in bottom. Delivered with matching belt.

You can wear the dress over a pair of tight pants with stilettos. Style it with oversized accessories, e.g. 3 large bracelets in different materials and a pair of hot oversized earrings.

Replace the matching belt with a leather look belt like carmakoma\'s style Rollercoaster to toughen up the look.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Halterneck top Daiquiries, Leggings Luxx, Belt Rollercoaster, Scarf Birdyscarf.

Fit: Super hot and slimming fit.

Length in cm: XS:100 / S:101,5 / M:103 / L:104,5.

Fabric: 100% cotton.

Colours: Black.',
    8503 => 'Description & styling tips by Weiss & Lykke:

Super elegant dress with many details.
Beautifully detailed v-neck with nice hem and sharply, pressed crease. Super nice sleeves and pockets in side seam.

The dress can be used for any occasion - both weekdays and parties. For a party look style it up with jewelry and stilettos. For weekdays wear it with tight pants, carmakoma\'s vest style Paris and a scarf around the neck.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Leggings Luxx, Belt Rollercoaster, Scarf Birdyscarf.

Fit: A bit A-shaped silhouette. Fits and gives a slimming look for all body structures.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5.

Fabric: 100% cotton.

Colours: Black.',
    8514 => 'Description & styling tips by Weiss & Lykke:

This petticoat is a basic item that any woman should begrudge themselves. carmakoma\'s petticoat has adjustable straps and the length fits most styles in this collection.
The quality is shiny with a smooth surface that works perfect underneath other fabrics. With this petticoat your dress will never "crawl" up your leg as you walk anymore.

Fit: Tight fit.

Length in cm: XS:83 / S:84,5 / M:86 / L:87,5.

Fabric: 100% polyester.

Colours: Black.',
    8510 => 'Description & styling tips by Weiss & Lykke:

Beautiful champagne coloured dress with graphic print across shoulder and chest. Print colours are coral, yellow, black and white. The tucked waistline completes the design. The dress has v-cut and short sleeves.

The dress can be used for any occasion. The beautiful print makes it festive and eye-catching. Perfect for parties. Style the dress with oversize jewelry. Wear large earrings and decorate the arm with big bracelets. Finish off with your favourite stilettos. If you don\'t prefer bare legs, then style the dress with nice stockings or leggings.
You can also dress it down for weekdays by wearing a pair of tight pants underneath.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Pants Roberto, Cardigan Jaume, Leggings Luxx, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5.

Fabric: Satin - 100% Polyester

Colours: Champagne colours with print.',
    8517 => 'Description & styling tips by Weiss & Lykke:
Beautiful and elegant dress with �-sleeves in nice and soft quality. Deep V-neck cut and hot cut with tucks below the chest.
It is possible to wear a petticoat underneath the dress as the quality is delicately thin.

For variation style the dress over leggings or pants.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Petticoat Muntaner, Pants Roberto, Belt Rollercoaster, Cardigan Carolina.

Fit: Great slimming fit.

Length in cm: XS:98 / S:99,5 / M:101 / L:102,5.

Fabric: 100% Tencel.

Colours: Dark grey.',
    8515 => 'Description & styling tips by Weiss & Lykke:
T-shirt dress with draped and deep neck cut. Tie cords for an adjustable neck opening.

The quality is super great, thin, and soft. It can be styled with a petticoat or a top over tight pants with stilettos.
Try a belt either around the hips or waist.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Jacket Snoop, Halterneck top Daquiries, Petticoat Mutaner, Pants Roberto, Leggings: Luxx, Belt Rollercoaster.

Fit: Loose fit.

Length in cm: XS:88 / S:89,5 / M:91 / L:92,5.

Fabric: 60% cotton, 40% rayon.

Colours: Black.',
    8532 => 'Description & styling tips by Weiss & Lykke:

Party top with super sexy open back and twisted detail. Cascading neck cut. Delivered with matching belt. Super great cotton quality.

For a less bare look it can be styled with a halterneck top or a t-shirt. For variation combine the top with a vest and a pair of tight pants.

For stylish combinations that go perfectly with this top, see carmakoma styles:
Pants Roberto, Halterneck top Daiquiries, Belt Rollercoaster, Scarf Birdyscarf.

Fit: Loose fit.

Length in cm: XS:71 / S:72,5 / M:74 / L:75,5.

Fabric: 100% cotton.

Colours: Black.',
    8518 => 'Description & styling tips by Weiss & Lykke:
Great jersey cardigan/bolero with hot details.
Slimming, crinkled sleeves. Pockets in side seam. Beautiful neck/collar. Extra length on back and cool creased effect along back.

The shape and cut of the cardigan makes it super nice with all kinds of dresses. You can also wear the cardigan with a pair of pants.

Wear a belt around the waist for variation. Wrap the front pieces over each other for a wrap-cardigan look.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Halterneck top Daquiries, Pants Roberto, Belt Rollercoaster.

Fit: Loose fit.

Length in cm (measured on back): XS:78 / S:79,5 / M:81 / L:82,5.

Fabric: 100% Tencel.

Colours: Dark grey.',
    8516 => 'Description & styling tips by Weiss & Lykke:
Hot cardigan with draped look, tight sleeves, deep v-neck cut and pockets. Buttons in front and along the neck cut.
The cardigan is delivered with matching belt. The quality is extremely nice and soft.

The cardigan is a perfect alternative for a jacket or blazer. Wear it over your shirt dresses for a stylish layered look. Combine with a pair of tight pants or leggings and a pair of stilettos. You can also try wearing a leather look belt around the waist like carmakoma\'s popular belt style Rollercoaster.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Halterneck top Daquiries, Pants Roberto, Leggings Luxx, Belt Rollercoaster, Scarf Birdyscarf and all shirts and dresses.

Fit: Loose fit.

Length in cm: XS:88 / S:89,5 / M:91 / L:92,5.

Fabric: 60% cotton, 40% rayon.

Colours: Black.',
    8533 => 'Description & styling tips by Weiss & Lykke:
Stylish tight pants. Cool zip detail along lower leg adding a creased effect. Pockets on hip. Closes with button in the waistline. The fit is perfect, especially on the back. Crotch length is high reaching the waist to avoid pressure on the soft part.

Super cool underneath various dresses and tops. Try replacing your leggings with pants style Roberto. Super hot with high heels.

Cotton quality with stretch effect. Gorgeous, soft and comfortable. Slightly shiny surface.

For stylish combinations that go perfectly with this dress, see carmakoma styles:
Dress Carmen, Jacket Snoop.

Fit: Tight fit.

Length in cm (measured inseam): XS:80 / S:80 / M:80 / L:80.

Fabric: 97% cotton 3% spandex.

Colours: Grey.',
    
    8585 => 'Description & styling tips by Weiss & Lykke:
An original carmakoma design in our popular cotton knit quality. A gorgeous cardigan with many details for women and girls of all ages.

Big buttons along sides. The high collar can be styled in many ways. Great, deep pockets. Long sleeves with high rib hem that gives slimming effect around the arms. Stylish knitted detail with rib along back. Wrinkly effect in front along fastening that closes with big press stud buttons.

The cardigan is a perfect supplement for the autumn wardrobe - it can be used as an alternative to a jacket. Can also be worn as a dress or over a tight pair of pants style Roberto 2. 

The collar can be styled in many ways: 1) buttoned all the way up 2) half open for a draped collar, 3) wear the collar as a hood/tube, 4) or try asymmetrical buttoning, which gives interesting effects with many different drapings. As an alternative carmakoma\'s wrap belt Rollercoaster will give you a super hot look.

Fit: Casual fit.

Length in cm: XS:92 / S:93,5 / M:95 / L:96,5

Fabric: 60% viscose 40% cotton

Colour: Black.',
    
    8586 => 'Description & styling tips by Weiss & Lykke:
Gorgeous and elegant knitted dress in carmakoma\'s popular cotton knit quality. The dress is knitted in organic shapes which gives great variation to the knit. Small puff sleeves and a beautiful cascade in front. All in all a fashionable dress for both weekdays and parties for stylish women and girls.

The dress can be combined with a pair of hot stockings and a pair of high heeled shoes. Also try carmakoma\'s popular halterneck top Daquiries.
For variation combine the dress with a pair of pants underneath - e.g. carmakoma\'s pants Roberto 2.
The dress is perfect for experimenting with lots of great accessories. It is an open invitation for creativity when picking out jewelry and accessories.

Fit: Casual fit.

Length in cm: XS:86 / S:87,5 / M:89 / L:90,5

Fabric: 60% viscose 40% cotton

Colour: Black.',
    
    8587 => 'Description & styling tips by Weiss & Lykke:
This super gourgeous, knitted dress is a mix of different kinds of yarn and knit patterns. It gives the knit a super gorgeous and exciting effect. Great open neck cut, sleeves with puff and high rib hems. Decorative buttons along the back give a tough twist. 

The dress is specially made for women with style and curves. It can be styled with a top or petticoat underneath the open knit. Try it with a halterneck top - e.g. style Daquiries or petticoat style Mutaner.
Also try wearing the dress over a pair of slim fit pants style Roberto 2 and a pair of pumps, which will turn your look more rock\'chick. If you want to spark up the dress with some colours, try a colourful top underneath or some coloured accessories. 

Fit: Casual fit.

Length in cm: XS:86 / S:87,5 / M:89 / L:90,5

Fabric: cotton/viscose mix

Colour: Black.',
    
    8588 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous dress in original carmakoma design - a true rock\'chick dress. 
The dress is a mix of different kinds of yarn and knit patterns - giving the knit a super cool and exciting effect.
Great, open neck cut allowing one bare shoulder. 
Sleeves with high rib hems.  

The dress can be styled with a top or petticoat underneath the open knit, e.g. our halterneck top style Daquiries or petticoat style Mutaner. Add a pair of hot stockings and elegant shoes and you are ready for a party.

If you want a tougher look the dress can be styled over a pair of slim fit pants style Roberto 2 and a pair of pumps to stress the rock\'chick look. 
Also try sparking up the dress with some colour - wear a colourful top underneath or some coloured accessories.
Fit: Casual fit.

Length in cm: XS:85 / S:86,5 / M:88 / L:89,5

Fabric: cotton/viscose mix

Colour: Black.',
    
    8589 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous and chic cardigan in original carmakoma design with big opportunities for personal styling. 
The cardigan is a mix of different kinds of yarn and knit patterns - giving the knit a super fashionable and exciting effect. 

Sleeves with rib hems, buttons on shoulder.

The cardigan can be worn in many different ways: 1) hanging loose - giving the cardigan extra front length 2) the right front piece buttoned on the shoulder with the left hanging loose 3) both front pieces buttoned. The cardigan has many trendy opportunities for styling whether you want length, draping or a cascade in front.
 
Wear the cardigan with a pair of jeans or slim fit pants style Roberto 2 or over a feminine dress. Create a stylish and trendy layered look.

Fit: Casual fit.

Length in cm: XS:61 / S:62,5 / M:64 / L:65,5

Fabric: cotton/viscose mix

Colour: Black.',
    
    8590 => 'Description & styling tips by Weiss & Lykke:
 
Gorgeous and sophisticated dress with great neck cut opening and sleeves with wrinkly effect. The dress is decorated with a "leather look" panel in front and back. 

The super gorgeous and exquisite jersey quality makes the dress well suited for both party and weekdays. 

For a party look the dress can be styled with a pair of chic stockings and elegant shoes. For variation wrap a belt around the waist style Rollercoaster or style the dress with a vest style Huntington for a rock\'chick look.

On weekdays the dress can be used over a pair of jeans or slim fit pants style Roberto 2. Style it with a nice scarf.

Fit: Slightly A-shaped, a loose fit over waist and hip.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5

Fabric: 100% viscose

Colour: Black.',
    
    8591 => 'Description & styling tips by Weiss & Lykke:
Gorgeous jersey dress with large, deep neck opening and sleeves with wrinkly effect. For women who love design and fashion.  

The super gorgeous and stylish jersey quality makes the dress well suited for both party and weekdays.

For a party look the dress can be styled with a pair of chic stockings and elegant shoes. For variation wrap a belt around the waist style Rollercoaster.

For weekdays wear the dress over a pair of jeans or slim fit pants style Roberto 2. Style it with a nice scarf. Our halterneck top style Daquiries or petticoat Mutaner go well underneath the dress.

Fit: Beautiful, feminine fit.

Length in cm: XS:90 / S:91,5 / M:93 / L:94,5

Fabric: 100% viscose

Colour: Black.',
    
    8592 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous and casual sweat-dress. Deep V-cut and tie cords along bottom. Long sleeves. The many cuts give a slimming effect for curvy women. Gorgeous tuck detail below bosom.
 
For weekdays you can wear the dress over a pair of jeans or slim fit pants style Roberto 2. Our halterneck top style Daquiries will look super nice underneath. For variation wrap a belt around the waist style Rollercoaster. The dress gives great opportunities for a trendy combination of the fashionable clothes especially for plus size women.

Fit: Casual fit.

Length in cm: XS:93 / S:94,5 / M:96 / L:97,5

Fabric: 100% cotton

Colour: Black.',
    
    8593 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous and casual sweat-dress. Terrific collar with deep V-cut and long sleeves. Special cuts give a slimming effect for women with curves. 

On weekdays you can wear the dress over a pair of jeans or slim fit pants style Roberto 2. Our halterneck top style Daquiries will look super nice underneath. For variation wrap a belt around the waist style Rollercoaster.

If you are tired of wearing your overcoat, try using the dress instead of a jacket - with a blouse underneath it will be chic as a pullover - get out the stilettos, wear a big, trendy bag over the shoulder and a nice scarf around the neck and you are ready for cool weather.

Fit: Casual fit.

Length in cm: XS:91 / S:92,5 / M:94 / L:95,5

Fabric: 100% cotton

Colour: Black.',
    
    8594 => 'Description & styling tips by Weiss & Lykke:
Ornamentation on clothes - chains among others - is a hit and a statement this season. Carmakoma\'s dress Chung is a sophisticated, gorgeous sweat-dress with a super nice neck decoration that will look hot for parties. 
The dress is trendy, feminine and sophisticated all at once. The back zip gives a tough twist. Carmakoma gives great opportunities of trendy combinations of the fashionable clothes especially for curvy women

The �-sleeved dress is designed with slimming cuts giving the dress a hot fit. 

For a party look the dress can be styled with a pair of chic stockings, elegant shoes or boots. Also try styling the dress with lots of large bracelets.

On weekdays wear the dress over a pair of tight pants style Roberto 2. Style with a nice scarf and a casual cardigan style Peaches giving you a hot luxury outfit. 

Fit: Casual, but feminine fit.

Length in cm: XS:93 / S:94,5 / M:96 / L:97,5

Fabric: 100% cotton

Colour: Black.',
    
    8595 => 'Description & styling tips by Weiss & Lykke:
Ornamentation on clothes is a hit and a statement this season. Carmakoma\'s dress Phoebe is a sophisticated and stylish sweat-dress with super gorgeous pearl decorations around the neck and on the sleeves. Great cut on back.
 
The dress is trendy, feminine, sophisticated with a twist of toughness all at once. 
The dress has �-sleeves and is designed with slimming cuts.

On weekdays the dress can be used over a pair of tight pants style Roberto 2. Try styling it with a nice scarf and a vest style Huntington for an everyday luxury look.

Fit: Feminine fit.

Length in cm: XS:92 / S:93,5 / M:95 / L:96,5

Fabric:  100% cotton

Colour: Black.',
    
    8596 => 'Description & styling tips by Weiss & Lykke:
 This sophisticated and beautiful blazer dress is part of carmakoma\'s perfect wardrobe for the curvy women with large personalities and a sense of design. The dress has a gorgeous neck opening and lapel. �-sleeves, big buttons. The dress is lined and has a super gorgeous fit. 

Wear the dress as a long blazer jacket with a shirt underneath or over a pair of tight pants style Roberto 2.
Also try styling it with a belt around the waist style Rollercoaster. 

Fit: Feminine fit.

Length in cm: XS:93 / S:94,5 / M:96 / L:97,5

Fabric: cotton: 97% cotton 3% spandex

Colour: Black.',
    
    8597 => 'Description & styling tips by Weiss & Lykke:
Sophisticated and super beautiful dress for the fashion conscious woman or girl. Gorgeous, deep V-neck cut opening. The small �-puff sleeve is fastened with metal press stud buttons on back. Gorgeous back opening. (The bra will not show).

The dress has a super gorgeous fit and is perfect for the woman or girl with beautiful curves.
 
For variation try styling with a belt around the waist style Rollercoaster. 

Fit: Feminine fit.

Length in cm: XS:93 / S:94,5 / M:96 / L:97,5

Fabric: 97% cotton 3% spandex

Colour: Black.',
    
    8598 => 'Description & styling tips by Weiss & Lykke:
Sophisticated and super beautiful carmakoma style dress. Gorgeous, deep V-neck cut opening. Long sleeve with a small puff. The dress closes with metal zip in front. 
The effect of the organic panels gives a fantastic silhouette.
Dress is lined and has a super gorgeous fit for all curvy women and girls.

The fabric surface has a shiny glow.

For variation try styling with a belt around the waist style Rollercoaster. 

Fit: Gorgeous and feminine fit.

Length in cm: XS:95 / S:96,5 / M:98 / L:99,5

Fabric: soft coated cotton//: 97% cotton 3% spandex

Colour: Black with shiny surface',
    
    8599 => 'Description & styling tips by Weiss & Lykke:
Chic, gorgeous jacket with a touch of army look.  �-sleeves and pockets. An original carmakoma design with many details. Perfect for the woman with curves and great sense of fashion. The jacket can be used both indoor and outdoors in mild weather.

Quilted detail along back. Crinkle detail along waist camouflaging waist and hip. Cool metal buttons. Closes with hooks in front.

The fabric is flexible with a shiny surface.

Fit: Incredible fit, especially across chest.

Fit: Gorgeous and feminine fit.

Length in cm: XS:66 / S:67,5 / M:69 / L:70,5

Fabric: Soft coaed cotton: 97% cotton 3% spandex

Colour: Black with shiny surface.',
    
    8600 => 'Description & styling tips by Weiss & Lykke:
Chic pants that tightens around the leg - a perfect alternative for leggings.
Tough-looking zip detail along the calf  with crinkle effects. Pockets on hip, buttons around the waist. Cool stud detail above the pocket.

The hot pants have a super fit that gives an attractive bottom.
High crotch height to avoid pressure on the soft spot of the belly, but giving a fantastic waistline.

The pants are super cool underneath various dresses and tops. A pair of high-heeled shoes will give a super gorgeous look

The fabric is flexible with a shiny surface.

Fit: Tight fit.

Length in cm: XS:80 / S:80 / M:80 / L:80

Fabric:  cotton: 97% cotton 3% spandex

Colour: Black with a shiny surface.',
    
    8601 => 'Description & styling tips by Weiss & Lykke:
 
Sophisticated and beautiful tunic dress in "leather look" material. Organically shaped panels gorgeously decorate the neck opening. Nice cross-stitches on both sleeves. �-sleeve with small puff.

The dress is lined and has a super gorgeous fit. 

For variation try styling it with a belt around the waist style Rollercoaster. 

The tunic dress goes well for both party and weekdays. It is perfect for women with a sense of design and fashion. For a party look style the dress with a pair of hot stockings, a stiletto and a pair of big earrings and you will look fantastic. 
On weekdays you can combine the dress with a pair of leggings or a pair of tight pants style Roberto 2. 

Fit: Gorgeous and feminine fit.

Length in cm: XS:93 / S:94,5 / M:96 / L:97,5

Colour: Black.',
    
    8602 => 'Description & styling tips by Weiss & Lykke:
A dress for both weekdays and parties in a soft and gorgeous "leather look" quality. Great sleeves and neck opening.

The dress is lined and has a super gorgeous fit.

For variation it can be styled with a belt around the waist style Rollercoaster. 

The dress is well suited for both party and weekdays. For parties the dress will look hot with a pair of chic stockings, a stiletto and a pair of big earrings or bracelets as accessories. 
On weekdays you can combine the dress with a pair of leggings or a pair of tight pants style Roberto 2. 

Fit: Gorgeous and feminine fit.

Length in cm: XS:91 / S:92,5 / M:94 / L:95,5

Colour: Black. ',
    
    8603 => 'Description & styling tips by Weiss & Lykke:
High waist slim line skirt in gorgeously soft "leather look" quality. Especially for curvy women and girls with a sense of fashion. 
The skirt is lined and has a super gorgeous fit. Crinkle effects around hip and waist camouflage the softer spots.

Fantastic detail along side hem with a mix of metal rivets and studs. 
Cool zip effect on back. 

Fit: High, tight-fitting waistline.

Length in cm: XS:57 / S:58 / M:59 / L:60

Colour: Black.',
    
    8604 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous jacket in soft, not too massive wool quality. The jacket has a slimming silhouette with many cool details. 
Big, fabric-covered buttons in front and along back. Big collar with different opportunities for styling.
Pockets and slimming sleeves. The jacket is lightly lined.

For variation you can style the jacket with a belt style Rollercoaster around the waist. The "leather look" belt against the wool gives a terrific contrasting effect. 

Fit: A-shaped, slimming cuts and fit.

Length in cm: XS:85 / S:86,5 / M:88 / L:89,5

Fabric: wool/viscose mix 

Colour: Black.',
    
    8605 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous t-shirt made of 100 % viscose. Chains on shoulders - a top with a statement. Difference in length between front and back. Small chest pocket. Stylish neck opening. 
The sleeves are long, but can be pulled up for a great look.
 
The shoulder chains make the top suitable for parties. Style it with a high waist skirt style Agyness or a pair of tight pants style Roberto 2.
Try combining the top with a pair of spotted stockings - a cute combination for trendy women and girls.

Our tubed scarf style Jingle is made in matching quality with chains. Therefore it is a super cool supplement for the U2-top. 

Fit: Casual fit.

Length in cm: XS:80 / S:81,5 / M:83 / L:84,5

Fabric: 100% viscose

Colour: Black with metal chains.',
    
    8606 => 'Description & styling tips by Weiss & Lykke:
Carmakoma\'s new tube scarf is a must-have-item with decorative chains and ribbon for the fashion conscious women and girls.
Can be wrapped around the neck as a regular scarf. Or leave it hanging draped around the neck. A third option is to wear the scarf as a hood.

The scarf can be styled with anything and will give you a sophisticated look. Wear it both indoor and outdoors on weekdays and for parties. 
The scarf can also be used as a poncho over the arms if you feel bare. The circle shaped design gives you endless opportunities for playing and experimenting with the scarf. 

One size: Length 70 cm

Fabric: 100% viscose

Colour: Black with metal chains.',
    
    8607 => 'Description & styling tips by Weiss & Lykke:
Carmakoma\'s rib knitted, fingerless gloves are a must-have-item. The gloves have extra length covering the arms. Stylish decorative buttons.

Wear the gloves with your overcoat - but also with your �-sleeved jackets - extending the sleeves and making you able to wear it even longer as the weather turns cooler.

Wear the gloves as sleeve extensions to your tops or dresses. Wear them both indoor and outdoors. Carmakoma gives large opportunities for trendy combinations of the clothes for curvy women.

One size: Length 50 cm

Fabric: soft wool mix

Colour: Black with silver coloured buttons.',
     
    8608 => 'Description & styling tips by Weiss & Lykke:
Carmakoma\'s vest is super gorgeous with decorations of stones and studs. Closes with tie-strings. Strong cotton lace on back contrasting the rest of the vest beautifully. A gorgeous mix of toughness and feminine details.
The vest can be combined with almost anything and will spark up any outfit. Wear it over a dress or top and with a pair of pants style Roberto 2. 

Length in cm: XS:45 / S:46,5 / M:48 / L:49,5

Fabric: 100% cotton

Colour: Black',
     
    8609 => 'Description & styling tips by Weiss & Lykke:
 Sophisticated and beautiful dress in a mix of different materials and colours. 
Gorgeous decoration of organically shaped panels along the neck cut and on the shoulders. 

For variation try styling the dress with a belt around the waist style Rollercoaster. 

Fit: Gorgeous and feminine fit.

Length in cm: XS:100 / S:101,5 / M:103 / L:104,5

Colour: Black.',
     
    8622 => 'Description & styling tips by Weiss & Lykke:
Beautiful, classical shirt dress with stylish details. Gorgeous, round neck opening with decorative tuck. Long sleeves.
Pockets in side hem.
Small puff effect at bottom.
Includes matching belt, but also try styling the dress with carmakoma\'s popular belt style Rollercoaster.

Fit: Incredibly beautiful fit. Fits all body types and gives a slimming effect.

Length in cm: XS: 100�/ S: 101,5�/ M: 103�/ L: 104,5


Composition:  95% cotton 5% lycra.

Colour: Black.',
     
    8624 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous cardigan with many great details.
Creased sleeves. Small fashionable fabric-covered buttons in front and button detail on back.

The neck opening is designed to vary between two different looks. 
With a buttoned cardigan the neck opening shapes like a waterfall.
When it is unbuttoned the cardigan has a beautiful draped look.

Red is this season\'s hot colour. Style Lagerfelt has the perfect red tone.

To complete the draped look style the cardigan with carmakoma\'s tubed scarf style Jingle. And try placing carmakoma\'s popular wrap-belt style Rollercoaster around the waist.

Fit: Beautiful, draped fit. Great for all body types.

Length in cm: XS: 65�/ S: 66,5�/ M: 68�/ L: 69,5

Composition: 100% viscose.

Colour: Red',
     
    8625 => 'Description & styling tips by Weiss & Lykke:
Long, beautiful and feminine top with many details.

Super hot sleeve with creased details adding a slimming effekt.
Difference in length between front and back.

Red is this season\'s hot colour. Style Portman has the perfect red tone.

Gorgeous and soft quality.

Style the top with carmakoma\'s fabulous slim pants style Roberto 2 for a cool rock\'chick look and place a belt around the waist. Also try carmakoma\'s stunning military inspired jacket style Lancome.

Fit: 
Beautiful spacious fit. Fits all body types.

Length in cm: XS: 85�/ S: 86,5�/ M: 88�/ L: 89,5

Composition:  100% viscose.

Colour: Red.',
     
    8626 => 'Description & styling tips by Weiss & Lykke:
Super gorgeous shirt blouse with many beautiful details. Fantastic quality in soft cotton composition. Lovely tucks along front and on back.
Closes with beautiful buttons inlaid with mother of pearl.
Stylish print on back.
Great sleeve capacity. Sleeves finish off with small rim.

This shirt can be styled with almost anything. Skirts, trousers or a vest.
Your only limit is your imagination.

Try a belt around the waist and suddenly your shirt has changed completely to a tight-fitting look. You can also try combining the shirt with carmakoma\'s skirt style Agyness for a terrifically beautiful look appropriate for any occasion. The perfect mix between a party/weekday outfit.

Fit: Beautiful draped fit. Great for all body types. 

Length in cm: XS: 90�/ S: 91,5�/ M: 93�/ L: 94,4

Composition: 100% cotton.

Colour: Dusty rose.',
     
    8627 => 'Description & styling tips by Weiss & Lykke:
A black tight-fitting skirt is a must-have in the wardrobe of any woman. It is a basic piece of clothing that can rescue you in any situation and that no woman should be without.

Style Rosie has a perfect fit and a strong quality so you really feel the skirt against your body. The tight fit helps shape your body. Wide elastic band around the waist.

The skirt is sewn in panels for a gorgeous effect.

Place the skirt around the waist according to the wanted length. It is beautiful when it reaches the knees.

Opportunities for styling are endless - it can be used for both parties and on weekdays. Style it with carmakoma\'s petticoat style Mutaner (as shown on the picture) and with carmakoma\'s tubed scarf style Jingle for a sexy and pure style look - and you are ready for partying.

On weekdays you can wear it with a pair of leggings and a shirt - e.g. style Van Gogh - which is a perfect look either on the job or for a glass of wine after work with your colleagues or girlfriends.
Style it with a couple of stylish bracelets and large earrings.

Fit: Tight-fitting.

Length in cm: XS: 56�/ S: 57�/ M: 58�/ L: 59

Composition: 90% viscose 6% polyester 4% elastane 

Colour: Black.',
    
    8628 => 'Description & styling tips by Weiss & Lykke:
 This season\'s must-have and key item is the soft, super hot knitted poncho with long fringes.
Can easily replace a jacket in mild weather. On cold days wear the poncho over a jacket and remember the long gloves style Piff for a perfect styling.

The poncho has a gorgeous, large polo neck collar, a fine knitted hole pattern along front and back. The knitted pattern adds a beautiful, stribed asymmetrical effect.

Fit: A bit A-shaped. Fits all body types.

Length in cm: XS: 90�/ S: 91,5�/ M: 93�/ L: 94,5

Composition:  100% cotton.

Colour: Grey melange.',
     
    8629 => 'Description & styling tips by Weiss & Lykke:
Super stylish shirt designed in simple lines, but with an eye for beautiful details.
Small tucks across the chest and elegant, deep neck cut. 
Buttons in contrasting colours inlaid with mother of pearl.

Difference in length between front and back.

Perfect for both weekdays and party. The shirt can be combined with both skirts and trousers.

Fit: A bit A-shaped. Fits all body types.

Length in cm: XS:71 / S: 72,5�/ M: 74�/ L: 75,5�

Colour: Black.'
    
    
        );

        if(isset($tr[$id])) return $tr[$id];
    
        return NULL;
        
    }
    
}
?>