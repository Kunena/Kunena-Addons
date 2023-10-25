<?php

/**
 * Kunena Search Module
 *
 * @package       Kunena.mod_kunenasearch
 *
 * @Copyright (C) 2008 - 2023 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Module\KunenaSearch\Site;

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Module\KunenaModule;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Joomla\CMS\Helper\ModuleHelper;

/**
 * Class ModuleKunenaSearch
 */
class ModuleKunenaSearch extends KunenaModule
{
    protected static $css = '/modules/mod_kunenasearch/tmpl/css/kunenasearch.css';

    public $ksearch_button;

    public $ksearch_button_pos;

    public $ksearch_button_txt;

    public $ksearch_width;

    public $ksearch_maxlength;

    public $ksearch_txt;

    public $ksearch_moduleclass_sfx;

    public $url;

    protected function _display(): void
    {
        $this->ksearch_button          = $this->params->get('ksearch_button', '');
        $this->ksearch_button_pos      = $this->params->get('ksearch_button_pos', 'right');
        $this->ksearch_button_txt      = $this->params->get('ksearch_button_txt', Text::_('Search'));
        $this->ksearch_width           = intval($this->params->get('ksearch_width', 20));
        $this->ksearch_maxlength       = $this->ksearch_width > 20 ? $this->ksearch_width : 20;
        $this->ksearch_txt             = $this->params->get('ksearch_txt', Text::_('Search...'));
        $this->ksearch_moduleclass_sfx = $this->params->get('moduleclass_sfx', '');
        $this->url                     = KunenaRoute::_('index.php?option=com_kunena');

        require ModuleHelper::getLayoutPath('mod_kunenasearch');
    }
}
