<?php
/**
 * Kunena Statistics Module
 * @package Kunena.mod_kunenastats
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Class ModuleKunenaStats
 */
class ModuleKunenaStats extends KunenaModule {
	static protected $css = '/modules/mod_kunenastats/tmpl/css/kunenastats.css';

	protected $api = null;
	protected $type = null;
	protected $items = 0;
	protected $stats = null;
	protected $titleHeader = '';
	protected $valueHeader = '';
	protected $top = 0;

	protected function _display() {
		$this->type = $this->params->get('type', 'general');
		$this->items = (int) $this->params->get('items', 5);
		$this->stats_link = $this->_getStatsLink(JText::_('MOD_KUNENASTATS_LINK'), JText::_('MOD_KUNENASTATS_LINK'));

		$this->stats = $this->getStats();
		require JModuleHelper::getLayoutPath('mod_kunenastats');
	}

	protected function getStats() {
		$stats = KunenaForumStatistics::getInstance ( );

		switch ($this->type) {
			case 'topics':
				$this->titleHeader = JText::_('MOD_KUNENASTATS_TOPTOPICS');
				$this->valueHeader = JText::_('MOD_KUNENASTATS_HITS');
				$items = $stats->loadTopTopics( $this->items );
				break;
			case 'posters':
				$this->titleHeader = JText::_('MOD_KUNENASTATS_TOPPOSTERS');
				$this->valueHeader = JText::_('MOD_KUNENASTATS_POSTS');
				$items = $stats->loadTopPosters( $this->items );
				break;
			case 'profiles':
				$this->titleHeader = JText::_('MOD_KUNENASTATS_TOPPROFILES');
				$this->valueHeader = JText::_('MOD_KUNENASTATS_HITS');
				$items = $stats->loadTopProfiles( $this->items );
				break;
			case 'polls':
				$this->titleHeader = JText::_('MOD_KUNENASTATS_TOPPOLLS');
				$this->valueHeader = JText::_('MOD_KUNENASTATS_VOTES');
				$items = $stats->loadTopPolls( $this->items );
				break;
			case 'thanks':
				$this->titleHeader = JText::_('MOD_KUNENASTATS_TOPTHANKS');
				$this->valueHeader = JText::_('MOD_KUNENASTATS_THANKS');
				$items = $stats->loadTopThankyous( $this->items );
				break;
			default:
				$this->type = 'general';
				$stats->loadGeneral(true);
				$this->latestMemberLink = KunenaFactory::getUser(intval($stats->lastUserId))->getLink();
				$this->userlist = $this->_getUserListLink('', $this->formatLargeNumber($stats->memberCount, 4));
				$items = $stats;
		}
		return $items;
	}

	public function shortenLink($link, $len) {
		return preg_replace('/>([^<]{'.$len.'})[^<]*</u', '>\1...<', $link);
	}

	/**
	 * This function formats a number to n significant digits when above
	 * 10,000. Starting at 10,0000 the out put changes to 10k, starting
	 * at 1,000,000 the output switches to 1m. Both k and m are defined
	 * in the language file. The significant digits are used to limit the
	 * number of digits displayed when in 10k or 1m mode.
	 *
	 * @param int $number 		Number to be formated
	 * @param int $precision	Significant digits for output
	 */
	public function formatLargeNumber($number, $precision = 3) {
		$output = '';
		// Do we need to reduce the number of significant digits?
		if ($number >= 10000){
			// Round the number to n significant digits
			$number = round ($number, -1*(log10($number)+1) + $precision);
		}

		if ($number < 10000) {
			$output = $number;
		} elseif ($number >= 1000000) {
			$output = $number / 1000000 . JText::_('COM_KUNENA_MILLION');
		} else {
			$output = $number / 1000 . JText::_('COM_KUNENA_THOUSAND');
		}

		return $output;
	}

	protected function _getUserListLink($action, $name, $title = null, $rel = 'nofollow'){
		$profile = KunenaFactory::getProfile ();
		$link = $profile->getUserListURL ( $action, true );
		return "<a href=\"{$link}\" title=\"{$title}\" rel=\"{$rel}\">{$name}</a>";
	}

	protected function _getStatsLink($name, $title = null, $rel = 'follow') {
		$link = KunenaRoute::_ ( 'index.php?option=com_kunena&view=stats' );

		return "<a href=\"{$link}\" title=\"{$title}\" rel=\"{$rel}\">{$name}</a>";
	}
}
