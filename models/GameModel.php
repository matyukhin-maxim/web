<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 08.08.16
 * Time: 19:58
 */
class GameModel extends CModel {
	
	public function getInfo($guid) {
		
		$data = $this->select('SELECT * FROM games WHERE uid = :id', ['id' => $guid]);
		$key = $this->select('SELECT icon FROM solutions WHERE gameid = :gid', ['gid' => $guid]);

		if (count($key) !== 3) return false;

		$data = get_param($data, 0);
		$data['key'] = array_column($key, 'icon');
		return $data;
	}

	public function clearDB() {

		$this->select('truncate table games');
		$this->select('truncate table images');
	}

	public function addImage($basename, $data) {

		$this->select('REPLACE INTO images (image, filename) VALUE (:img, :fname)', [
			'img' => $data,
			'fname' => $basename,
		]);
	}

	public function getLibrary() {

		return $this->select('SELECT id, filename FROM images');
	}

	public function getImages($count = 10) {

		return $this->select('
			SELECT id, image, filename 
			FROM images 
			WHERE deleted = 0 
			ORDER BY rand() 
			LIMIT :cnt', ['cnt' => $count]);
	}

	public function createGame($gameID) {

		$this->select('DELETE FROM solutions WHERE gameid = :gid', ['gid' => $gameID]);
		$answer = $this->getImages(3);
		foreach ($answer as $icon)
			$this->select('INSERT INTO solutions (gameid, icon) VALUE (:gid, :img)', [
				'gid' => $gameID,
				'img' => get_param($icon, 'id'),
			]);

		$this->select('INSERT INTO games (uid, dstart) VALUE (:gid, now())', ['gid' => $gameID]);
		return $this->getInfo($gameID);
	}

	public function getIconSet($total) {

		if (!is_array($total) || empty($total)) return [];
		$list = $this->select('select id, image, filename from images where id in :ilist', [
			'ilist' => $total,
		]);

		return $list;
	}
}