<?php

/**
 * Kunena Search Module
 *
 * @package       Kunena.mod_kunenasearch
 *
 * @Copyright (C) 2008 - 2025 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

?>

<form action="<?php echo $this->url ?>" method="post" id="ksearch-form" name="ksearch-form">
    <input type="hidden" name="view" value="search" />
    <input type="hidden" name="task" value="results" />

    <div class="ksearch<?php echo $this->ksearch_moduleclass_sfx; ?>">
        <fieldset class="ksearch-fieldset">
            <legend class="ksearch-legend"><?php echo Text::_('MOD_KUNENASEARCH_SEARCHBY_KEYWORD'); ?></legend>
            <label class="ksearch-label" for="ksearch-keywords"><?php echo Text::_('MOD_KUNENASEARCH_SEARCH_KEYWORDS'); ?>:</label>
            <?php // Kunena reads the words of a search from "query"; the hint is a placeholder (not a value which would be searched). ?>
            <input id="ksearch-keywords" type="search" class="ks kinput" name="query" size="<?php echo $this->ksearch_width; ?>"
                   placeholder="<?php echo htmlspecialchars($this->ksearch_txt, ENT_QUOTES, 'UTF-8'); ?>" />
            <input id="ksearch-keywordfilter" type="hidden" name="titleonly" value="0" />
            <?php
            if ($this->ksearch_button == 1) {
                if ($this->ksearch_button_pos == 'bottom') {
                    echo '<br />';
                }

                echo '<input type="submit" value="' . htmlspecialchars($this->ksearch_button_txt, ENT_QUOTES, 'UTF-8') . '" class="kbutton btn' . $this->ksearch_moduleclass_sfx . '" />';
            };
            ?>
        </fieldset>
    </div>
</form>
