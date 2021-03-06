<?php
namespace pl\fe\matter\text;

require_once dirname(dirname(__FILE__)) . '/base.php';

class main extends \pl\fe\matter\base {
	/**
	 *
	 */
	public function index_action() {
		\TPL::output('/pl/fe/matter/text/frame');
		exit;
	}
	/**
	 *
	 */
	public function setting_action() {
		\TPL::output('/pl/fe/matter/text/frame');
		exit;
	}
	/**
	 *
	 */
	public function list_action($site, $fields = '*') {
		if (false === ($user = $this->accountUser())) {
			return new \ResponseTimeout();
		}

		$q = [
			$fields,
			'xxt_text',
			["siteid" => $site, "state" => 1],
		];
		$q2['o'] = 'create_at desc';
		$texts = $this->model()->query_objs_ss($q, $q2);

		return new \ResponseData($texts);
	}
	/**
	 * 创建文本素材
	 */
	public function create_action($site) {
		if (false === ($user = $this->accountUser())) {
			return new \ResponseTimeout();
		}

		$model = $this->model();
		$text = $this->getPostJson();

		$d = array();
		$d['siteid'] = $site;
		$d['creater'] = $user->id;
		$d['creater_name'] = $user->name;
		$d['create_at'] = time();
		$d['modifier'] = $user->id;
		$d['modifier_name'] = $user->name;
		$d['modify_at'] = time();
		$d['title'] = $model->escape($text->title);
		// @todo should remove
		$d['content'] = $model->escape($text->title);

		$id = $model->insert('xxt_text', $d, true);

		$q = [
			"*",
			'xxt_text',
			["id" => $id],
		];
		$text = $this->model()->query_obj_ss($q);

		return new \ResponseData($text);
	}
	/**
	 *
	 */
	public function delete_action($site, $id) {
		if (false === ($user = $this->accountUser())) {
			return new \ResponseTimeout();
		}

		$model = $this->model();
		$nv = new \stdClass;
		$nv->state = 0;
		$nv->modifier = $user->id;
		$nv->modifier_name = $user->name;
		$nv->modify_at = time();

		$rst = $model->update(
			'xxt_text',
			$nv,
			["siteid" => $site, "id" => $id]
		);

		return new \ResponseError($rst);
	}
	/**
	 * 更新文本素材的属性
	 */
	public function update_action($site, $id) {
		if (false === ($user = $this->accountUser())) {
			return new \ResponseTimeout();
		}

		$model = $this->model();
		$nv = $this->getPostJson();

		if (isset($nv->title)) {
			$nv->title = $model->escape($nv->title);
			$nv->content = $nv->title;
		}
		$nv->modifier = $user->id;
		$nv->modifier_name = $user->name;
		$nv->modify_at = time();

		$rst = $model->update(
			'xxt_text',
			$nv,
			["siteid" => $site, "id" => $id]
		);

		return new \ResponseData($rst);
	}
}