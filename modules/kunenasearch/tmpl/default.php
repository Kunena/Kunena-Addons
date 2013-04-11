<?php
/**
 * Kunena Search Module
 * @package Kunena.mod_kunenasearch
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>

<form action="<?php echo $this->url ?>" method="post" id="ksearch-form" name="ksearch-form">
	<input type="hidden" name="view" value="search" />
	<input type="hidden" name="task" value="results" />
	<div class="ksearch<?php echo $this->ksearch_moduleclass_sfx; ?>">
		<fieldset class="ksearch-fieldset">
			<legend class="ksearch-legend"><?php echo JText::_('MOD_KUNENASEARCH_SEARCHBY_KEYWORD'); ?></legend>
			<label class="ksearch-label" for="ksearch-keywords"><?php echo JText::_('MOD_KUNENASEARCH_SEARCH_KEYWORDS'); ?>:</label>
			<input id="ksearch-keywords" type="text" class="ks kinput" name="q" size="<?php echo $this->ksearch_width; ?>" value="<?php echo $this->ksearch_txt; ?>" onblur="if(this.value=='') this.value='<?php echo $this->ksearch_txt; ?>';" onfocus="if(this.value=='<?php echo $this->ksearch_txt; ?>') this.value='';"  />
			<input id="ksearch-keywordfilter" type="hidden" name="titleonly" value="0" />
				<?php
					if ($this->ksearch_button==1){
						if ($this->ksearch_button_pos=='bottom'){
						echo '<br />';
						}
					echo '<input type="submit" value="'.$this->ksearch_button_txt.'" class="kbutton'.$this->ksearch_moduleclass_sfx.'" />';
					};
				?>
		</fieldset>
	</div>
</form>