<?php

/**
 * Kunena Latest Module
 *
 * @package       Kunena.mod_kunenalatest
 *
 * @Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();
?>
<div class="<?php echo $this->params->get('moduleclass_sfx') ?> klatest <?php echo $this->params->get('sh_moduleshowtype') ?>">
    <ul class="klatest-items">
        <?php if (empty($this->topics)) :
            ?>
            <li class="klatest-item"><?php echo Text::_('MOD_KUNENALATEST_NO_MESSAGE') ?></li>
        <?php else :
            ?>
            <?php $this->displayRows(); ?>
        <?php endif; ?>
    </ul>
    <?php if ($this->topics && $this->params->get('sh_morelink')) :
        ?>
        <p class="klatest-more"><?php echo HTMLHelper::_('kunenaforum.link', $this->params->get('moreuri'), Text::_('MOD_KUNENALATEST_MORE_LINK')); ?></p>
    <?php endif; ?>
</div>
