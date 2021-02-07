<?php
/**
 *
 * @package Locked Topics at End Extension
 * @copyright (c) 2015 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\lockatend\migrations;

class version_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return [
			['config.add', ['lockatend_user_enable', '1']],
			['config.add', ['lockatend_version', '1.0.0']],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_lock_at_end' => ['BOOL', 1],
				],
			],
		];
	}

	/**
	 * Drop the columns schema from the tables
	 *
	 * @return array Array of table schema
	 * @access public
	 */
	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'users' => [
					'user_lock_at_end',
				],
			],
		];
	}
}
