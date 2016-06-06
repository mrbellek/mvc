<?php
namespace MVC\Helper;

class Pagination {

	private $sBaseUrl;	//base page url for links
	private $iPerPage;	//items per page
	private $iTotal;	//total items available
	private $iMaxRoom;	//how many spaces to show around current page before hiding behind ellipsis

	public function __construct($iTotal, $iPerPage = 30, $iMaxRoom = 10) {

		$this->sBaseUrl = $this->getCaller();
		$this->iTotal = $iTotal;
		$this->iPerPage = $iPerPage;
		$this->iMaxRoom = $iMaxRoom;
	}

	public function getPage($aObjects, $iPage) {

		$aVars = $this->getVars($iPage);
		$iOffset = ($aVars['current'] - 1) * $this->iPerPage;

		return array_slice($aObjects, $iOffset, $this->iPerPage, TRUE);
	}

	public function getVars($iPage) {

		//calculate first & last page, sanitize user inputted current page nummer
		$iLast = ceil($this->iTotal / $this->iPerPage);
		$iFirst = 1;
		$iPage = ($iPage < $iFirst || $iPage > $iLast ? $iFirst : $iPage);

		return array(
			'base'		=> $this->sBaseUrl,
			'first'		=> $iFirst,
			'last'		=> $iLast,
			'current'	=> $iPage,
			'maxroom'	=> $this->iMaxRoom,
		);
	}

	private function getCaller() {

		$aBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$sCaller = '';
		if (!empty($aBacktrace[2])) {
			$sClass = strtolower(str_replace('MVC\\Controller\\', '', $aBacktrace[2]['class']));
			$sFunction = $aBacktrace[2]['function'];
			$sCaller = '/' . $sClass . '/' . $sFunction . '/';
		}

		return $sCaller;
	}
}
