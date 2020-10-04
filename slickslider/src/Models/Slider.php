<?php
/**

 * NOTICE OF LICENSE

 *

 * This file is licenced under the Software License Agreement.

 * With the purchase or the installation of the software in your application

 * you accept the licence agreement.

 *

 * You must not modify, adapt or create derivative works of this source code

 *

 *  @author    Fabio Manzo

 *  @copyright 2010-2015

 *  @license   free

 */

namespace Slickslider\Models;

use PrestaShop\PrestaShop\Adapter\Entity\ObjectModel;

class Slider extends ObjectModel
{
    public $logo;
    public $paragrafo;
    public $colore;
    public $sfondo;

    public static $definition = array(
        'table' => 'slickslider',
        'primary' => 'id_slickslider',
        'multilang' => true,
        'fields' => array(
        'logo' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 255),
        'sfondo' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 255),
        'colore' => array('type' => self::TYPE_STRING, 'lang' => false, 'validate' => 'isCleanHtml', 'size' => 20),

        // Lang fields
        'paragrafo' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
        )
    );
}
