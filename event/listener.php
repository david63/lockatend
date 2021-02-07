<?php
/**
 *
 * @package Locked Topics at End Extension
 * @copyright (c) 2015 david63
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace david63\lockatend\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use phpbb\config\config;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/**
	 * Constructor for listener
	 *
	 * @param config     $config     Config object
	 * @param request    $request    Request object
	 * @param template   $template   Template object
	 * @param user       $user       User object
	 *
	 * @access public
	 */
	public function __construct(config $config, request $request, template $template, user $user)
	{
		$this->config   = $config;
		$this->request  = $request;
		$this->template = $template;
		$this->user     = $user;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.acp_board_config_edit_add' 	=> 'acp_board_settings',
			'core.ucp_prefs_view_data' 			=> 'add_user_prefs',
			'core.ucp_prefs_view_update_data' 	=> 'update_user_prefs',
			'core.viewforum_get_topic_ids_data'	=> 'update_viewforum_sql_ary',
			'core.mcp_view_forum_modify_sql' 	=> 'update_mcp_sql_ary',
		];
	}

	/**
	 * Set ACP board settings
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function acp_board_settings($event)
	{
		if ($event['mode'] == 'post')
		{
			$new_display_var = [
				'title' => $event['display_vars']['title'],
				'vars' => [],
			];

			foreach ($event['display_vars']['vars'] as $key => $content)
			{
				$new_display_var['vars'][$key] = $content;
				if ($key == 'posts_per_page')
				{
					$new_display_var['vars']['lockatend_user_enable'] = [
						'lang' => 'LOCK_AT_END_ENABLE',
						'validate' => 'bool',
						'type' => 'radio:yes_no',
						'explain' => true,
					];
				}
			}
			$event->offsetSet('display_vars', $new_display_var);
		}
	}

	/**
	 * Add the necessay variables
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function add_user_prefs($event)
	{
		if ($this->config['lockatend_user_enable'])
		{
			$data = $event['data'];

			$data = array_merge($data, [
				'lock_at_end' => $this->request->variable('lock_at_end', (!empty($user->data['user_lock_at_end'])) ? $user->data['user_lock_at_end'] : 0),
			]);

			$event->offsetSet('data', $data);
		}

		$this->template->assign_vars([
			'S_LOCK_AT_END' => $this->user->data['user_lock_at_end'],
			'S_USER_ENABLE' => $this->config['lockatend_user_enable'],
		]);
	}

	/**
	 * Update the sql data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function update_user_prefs($event)
	{
		if ($this->config['lockatend_user_enable'])
		{
			$sql_ary = $event['sql_ary'];
			$data    = $event['data'];

			$sql_ary = array_merge($sql_ary, [
				'user_lock_at_end' => $data['lock_at_end'],
			]);

			$event->offsetSet('sql_ary', $sql_ary);
		}
	}

	/**
	 * Update the sql data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function update_viewforum_sql_ary($event)
	{
		if (($this->config['lockatend_user_enable'] && $this->user->data['user_lock_at_end']) || !$this->config['lockatend_user_enable'])
		{
			$sql_ary             = $event['sql_ary'];
			$store_reverse       = $event['store_reverse'];
			$sql_sort_order      = $event['sql_sort_order'];
			$sql_ary['ORDER_BY'] = 't.topic_status ' . ((!$store_reverse) ? 'ASC' : 'DESC') . ', ' . $sql_sort_order;
			$event['sql_ary']    = $sql_ary;
		}
	}

	/**
	 * Update the sql data
	 *
	 * @param object $event The event object
	 * @return null
	 * @access public
	 */
	public function update_mcp_sql_ary($event)
	{
		if (($this->config['lockatend_user_enable'] && $this->user->data['user_lock_at_end']) || !$this->config['lockatend_user_enable'])
		{
			$sql          = $event['sql'];
			$event['sql'] = str_replace('t.topic_last_post_time', 't.topic_status ASC, t.topic_last_post_time', $sql);
		}
	}
}
