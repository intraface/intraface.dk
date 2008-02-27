<?php

/**
 * Klassen skal returnere et array med et sitemap
 * Desuden skal klassen kunne lave et google-sitemap
 * Det er noget med at man skal have et eller andet på sin egen side?
 * sitemap skal nok pushes ud
 *
 * Klassen skal formentlig være inkluderet CMS_Site
 * Spørgsmålet er om der er meget forskel mellem Navigtion og SiteMap
 *
 * @package Intraface_CMS
 */

class CMS_SiteMap {

    private $cmssite;

    function __construct($cmssite)
    {
        $this->cmssite = $cmssite;
    }

    function build()
    {
        $cmspage = new CMS_Page($this->cmssite);
        return $cmspage->getList('page');
    }
}

/*
<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
        <url>
            <loc>http://www.vih.dk/nyheder/</loc>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    </urlset>

*/

?>
