<?php
namespace frontend\controllers;

use Yii;

/**
 * Site controller
 */
class BlogController extends Sceleton
{
	public function actionIndex()
	{
		global $Blog;


		$this->view->wp_head = $Blog->head();
		return $this->render('index.tpl', [
			'page_name' => 'blog'
		]);
	}
}
