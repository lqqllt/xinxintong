<?php
namespace site\fe\matter\news;

include_once dirname(dirname(__FILE__)) . '/base.php';
/**
 * 多图文
 */
class main extends \site\fe\matter\base {
	/**
	 *
	 */
	public function get_action($id, $cascade = 'Y') {
		$modelNews = $this->model('matter\news');
		$news = $modelNews->byId($id);
		
		if ($cascade === 'Y') {
			$news->matters = $modelNews->getMatters($news->id);
			foreach ($news->matters as &$m) {
				$matterModel = \TMS_APP::M('matter\\' . $m->type);
				$m->url = $matterModel->getEntryUrl($this->siteId, $m->id);
			}
			$news->acl = $this->model('acl')->byMatter($this->siteId, 'news', $news->id);
		}
		//缺省素材
		if (count($news->matters)===0 && $news->empty_reply_type && $news->empty_reply_id) {
			$r[0] = $this->model('matter\base')->getMatterInfoById($news->empty_reply_type, $news->empty_reply_id);
			$r[0]->url=\TMS_APP::M('matter\\' . $news->empty_reply_type)->getEntryUrl($this->siteId, $news->empty_reply_id);
			$news->matters=$r;
		}

		$data['news'] = $news;
		$data['user'] = $this->who;

		return new \ResponseData($data);
	}
}