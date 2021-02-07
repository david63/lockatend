<?php
/**
 *
 * @package Locked Topics at End Extension
 * @copyright (c) 2015 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\lockatend\migrations;

class version_1_0_1 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\david63\lockatend\migrations\version_1_0_0'];
	}

	public function update_data()
	{
		return [
			['config.remove', ['lockatend_version']],
		];
	}
}
