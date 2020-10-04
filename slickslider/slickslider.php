<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/src/Models/Slider.php');


class Slickslider extends Module
{
    protected $config_form = false;
    protected $_html = '';

    public function __construct()
    {
        $this->name = 'slickslider';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Fabio Manzo';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Slick Slider');
        $this->description = $this->l('Questo modulo crea uno slider Slick');
        
        $this->confirmUninstall = $this->l('Disinstallando il modulo gli slider creati verranno persi. Vuoi procedere?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }


    public function install()
    {

        return parent::install() &&
            $this->registerHook('sliderSlick') &&
            $this->createTableSlick();
    }

    public function uninstall()
    {

        return $this->deletesTableSlick() && parent::uninstall();
    }

    /**
     * Loads the admin views
     */
    public function getContent()
    {
        
        $this->_html = '';
        if (((bool)Tools::isSubmit('submitSlickslider')) == true || Tools::isSubmit('deleteslickslider') == true) {
            $this->postProcess();
        } elseif (Tools::isSubmit('newslide')||(Tools::isSubmit('id_slickslider') && Tools::isSubmit('updateslickslider'))) {
            $this->_html .= $this->renderAddForm();
        } else {
            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
            $this->_html .= $output.$this->renderSlicksliderList();
        }

        return $this->_html;
    }


    protected function renderSlicksliderList()
    {
        $helper = new HelperList();
        
        $helper->title = $this->l("Slide");
        $helper->table = 'slickslider';
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_slickslider';
        $helper->actions = array('edit', 'delete');

        $slides = $this->getSlidesAdmin($this->context->language->id);
        $helper->listTotal = count($slides);
        $helper->tpl_vars = array('show_filters' => false);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->toolbar_btn['new'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&newslide=1&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Aggiungi nuova slide')

        );
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;


        return $helper->generateList($slides, [
            'id_slickslider' => array('title' => $this->l("ID"), 'type' => 'int', 'orderby' => false),
            'logo' =>           array('title' => $this->l("Logo"), 'type' => 'text', 'orderby' => false),
            'sfondo' =>         array('title' => $this->l("Sfondo"), 'type' => 'text', 'orderby' => false),
            'colore' =>         array('title' => $this->l("Colore"), 'type' => 'text', 'orderby' => false)
        ]);
    }


    public function renderAddForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l("Slide"),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Logo', array(), 'Admin.Global'),
                        'name' => 'logo',
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l("Sfondo"),
                        'name' => 'sfondo',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Colore', array(), 'Admin.Catalog.Feature'),
                        'name' => 'colore',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l("Descrizione"),
                        'name' => 'paragrafo',
                        'autoload_rte' => true,
                        'lang' => true,
                    )
                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Salva', array(), 'Admin.Actions'),
                )
            ),
        );

        if (Tools::isSubmit('id_slickslider')) {
            $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_slickslider');
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = 'slickslider';
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = 'id_slickslider';
        $helper->submit_action = 'submitSlickslider';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $this->getAddFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        $helper->override_folder = '/';

        $html = "";
        if ((bool)Tools::isSubmit('displayError') == 1) {
            // Warning sicuramente migliorabile, però almeno diamo un feedback nel caso in cui l'editor inserisce dei campi vuoti
            $html .= "<p style='color:red;margin-bottom:10px;'>I campi obbligatori non possono essere vuoti.</p>";
        }

        return $html.$helper->generateForm(array($fields_form));
    }
    

    public function getAddFieldsValues()
    {

        $languages = Language::getLanguages(false);

        $fields = array(
            'logo' => '',
            'sfondo' => '',
            'colore' => ''
        );

        if (Tools::isSubmit('id_slickslider')) {
            $slide = new \Slickslider\Models\Slider((int)Tools::getValue('id_slickslider'));
            $fields['id_slickslider'] = (int)Tools::getValue('id_slickslider', $slide->id);
            $fields['logo'] = $slide->logo;
            $fields['sfondo'] = $slide->sfondo;
            $fields['colore'] = $slide->colore;

            foreach ($languages as $lang) {
                $fields['paragrafo'][$lang['id_lang']] = Tools::getValue('paragrafo_'.(int)$lang['id_lang'], $slide->paragrafo[$lang['id_lang']]);
            }
        } else {
            // Nuova slide. Setto valori vuoti per smarty, per ogni lingua del paragrafo
            foreach ($languages as $lang) {
                $fields['paragrafo'][$lang['id_lang']] = "";
            }
        }

        return $fields;
    }



    /**
     * Creates the table during the installation
     */
    protected function createTableSlick()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'slickslider` (
              `id_slickslider` int(11) NOT NULL AUTO_INCREMENT,
              `logo` varchar(255) NOT NULL,
              `colore` varchar(10) NOT NULL,
              `sfondo` varchar(255) NOT NULL,
            PRIMARY KEY  (`id_slickslider`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
        
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'slickslider_lang` (
              `id_slickslider` int(11) NOT NULL,
              `id_lang` int(11) NOT NULL,
              `paragrafo` longtext NOT NULL,
            PRIMARY KEY  (`id_slickslider`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes the table when uninstall
     */
    protected function deletesTableSlick()
    {
        $sql = array();

        $sql[] = "DROP TABLE `" . _DB_PREFIX_ . "slickslider`";
        $sql[] = "DROP TABLE `" . _DB_PREFIX_ . "slickslider_lang`";


        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }

        return true;
    }


    /**
     * Validate all fields to complete: it just checks if the values are empty
     */
    protected function validateFields()
    {
        $fields = [
            'logo',
            'sfondo',
            'colore'
        ];

        foreach ($fields as $value) {
            if (empty(trim(Tools::getValue($value)))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {

        if (((bool)Tools::isSubmit('submitSlickslider')) == true) {
            if ($this->validateFields()) {
                if (Tools::isSubmit('id_slickslider')) {
                    $slider = new \Slickslider\Models\Slider(Tools::getValue('id_slickslider'));
                    $alert_type = '4'; // "aggiornamento riuscito"
                } else {
                    $slider = new \Slickslider\Models\Slider();
                    $alert_type = '3'; // "creato con successo"
                }
                
                $slider->logo = Tools::getValue('logo');
                $slider->sfondo = Tools::getValue('sfondo');
                $slider->colore = Tools::getValue('colore');
                $languages = Language::getLanguages(false);

                foreach ($languages as $language) {
                    $slider->paragrafo[$language['id_lang']] = Tools::getValue("paragrafo_".$language['id_lang']);
                }

                $slider->save();
            } else {
                // Error
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&newslide=1&displayError=1');
            }
        } elseif (((bool)Tools::isSubmit('deleteslickslider')) == true) {
            $slider = new \Slickslider\Models\Slider(Tools::getValue('id_slickslider'));
            $alert_type = '1'; // Cancellazione avvenuta con successo.

            $slider->delete();
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf='.$alert_type.'&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
    }


    /*
        The slides for the admin panel
    */
    public function getSlidesAdmin($id_lang)
    {
        $result = Db::getInstance()->executes("SELECT * FROM `" . _DB_PREFIX_ . "slickslider` s LEFT JOIN `" . _DB_PREFIX_ . "slickslider_lang` sl ON (s.`id_slickslider` = sl.`id_slickslider` AND sl.`id_lang` = " . (int)$id_lang . ")");
        return $result;
    }

    /*
        The slides for the template
    */
    public function getSlides()
    {
        $result = Db::getInstance()->executes("SELECT id_slickslider FROM `" . _DB_PREFIX_ . "slickslider`");

        $slides = [];
        foreach ($result as $id_slickslider) {
            $slides[] = new \Slickslider\Models\Slider($id_slickslider['id_slickslider'], $this->context->language->id);
        }
        return $slides;
    }

    /**
     * Custom hook to show in brands.tpl
     */
    public function hooksliderSlick()
    {
        
        $this->context->smarty->assign([
            'slicksliders' => $this->getSlides()
        ]);

        return $this->display(__FILE__, "slickslider.tpl");
    }
}
