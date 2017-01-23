<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 08.08.16
 * Time: 16:15
 *
 * @property GameModel $model
 */
class GameController extends CController {

	public function actionLoad() {

		$this->model->clearDB();
		foreach(glob('work/*.png') as $path) {
			
			$data = base64_encode(file_get_contents($path));
			$this->model->addImage(basename($path), $data);
		}

		var_dump($this->model->getLibrary());
	}

	public function actionIndex() {

		$this->scripts[] = 'game';
		$gameID = filter_input(INPUT_COOKIE, 'game', FILTER_SANITIZE_STRING);
		if (empty($gameID)) {

			// Только зашли, либо время жизни куки кончилось
			$gameID = md5(uniqid('random'));
			setcookie('game', $gameID, time() + 3600, '/');
		}

		$this->render('', false);

		$gInfo = $this->model->getInfo($gameID);
		if (!$gInfo) $gInfo = $this->model->createGame($gameID);

		//var_dump($gInfo);
		//// Ответ
		//$ans = get_param($gInfo, 'key');
		//// Возьмем X случайных иконок [с запасом]. Лишнее потом отрежем
		//$random = array_column($this->model->getImages(30), 'id');
		//// Уберем из них ответ (чтобы не повторяться)
		//$total = array_diff($random, $ans);
		//$total = $ans + $total; // и воставим его вперед, чтобы при обрезании не потерять
		//
		//$total = array_slice($total, 0, 15 + get_param($gInfo, 'level'));
		//$icons = $this->model->getIconSet($total);


		if (!$gInfo) {
			// Если создать не удалось, то явно косяк. Выходим

			$this->preparePopup('Чтото пошло не так.');
			$this->render('');
			return;
		};

		//$this->render('');
		$this->render('board');
		//$this->redirect([
		//	'location' => '/game/',
		//	'soft' => 1,
		//	'delay' => 10,
		//]);
	}

	public function actionGenerate() {

		$iWidth = 1024;
		$iHeight = 704;

		$iconSize = 64;
		$perRowX = $iWidth / $iconSize;
		$perRowY = $iHeight / $iconSize;

		/** todo Метод
		 * Нужно выбрать иконки-решения для данной игры (N штук)
		 * и (Х - N) случайных иконок иключив при этом повторения
		 * где Х - это общее число выводимых иконок зависящее от текущего раунда
		**/

		$gameID = filter_input(INPUT_COOKIE, 'game', FILTER_SANITIZE_STRING);
		$gInfo = $this->model->getInfo($gameID);

		header("Content-type: image/png");
		$im   = imagecreatetruecolor($iWidth, $iHeight);
		$back = imagecolorallocate($im, 190, 220, 190);
		$high = imagecolorallocatealpha($im, 255, 190, 50, 120);

		// Если нет информации по присланому ИДшнику. Либо вышел срок куки, либо ХАК ;)
		// В таком случае вернем просто пустую картинку
		if (!$gInfo) {

			imagefilledrectangle($im, 0, 0, $iWidth - 1, $iHeight - 1, $back);
			imagepng($im);
			imagedestroy($im);
			$this->preparePopup('Время раунда истекло');
			return;
		}

		// Правильный твет
		$ans = get_param($gInfo, 'key');
		// Возьмем X случайных иконок [50% заполение всей области].
		$cnt = intval($perRowX * $perRowY * 0.5);
		$random = array_column($this->model->getImages($cnt), 'id');
		// Уберем из них ответ (чтобы не повторяться)
		$total = array_diff($random, $ans);
		$total = $ans + $total; // и воставим его вперед, чтобы при обрезании не потерять

		//$total = array_slice($total, 0, 25 + get_param($gInfo, 'level'));
		$icons = $this->model->getIconSet($total);

		redraw:
		imagefilledrectangle($im, 0, 0, $iWidth - 1, $iHeight - 1, $back);

		// Создадим массив координатной сетки
		$coords = range(0, ($perRowX * $perRowY) - 1);
		shuffle($coords); // Перемешаем для рандомности

		$points = [];
		// Рисуем выбранные картинки в случайных координатах
		for ($idx = 0; $idx < count($icons); $idx++) {

			// $path = $this->images[ $indexes[$idx] ];
			// $icon = imagecreatefrompng($path);
			$icon = imagecreatefromstring(base64_decode($icons[$idx]['image']));
			$px = intval($coords[$idx] % $perRowX) * $iconSize;
			$py = intval($coords[$idx] / $perRowX) * $iconSize;

			// Если иконка из числа правельных ответов,
			// то запомним ее координаты для вывода линий-подсказок
			if (in_array($icons[$idx]['id'], $ans))	{

				$points[] = $px + $iconSize / 2;
				$points[] = $py + $iconSize / 2;
			}

			//imagepolygon($im, $points, count($points) / 2, $high);
			imagefilledpolygon($im, $points, count($points) / 2, $high);
			imagecopy($im, $icon, $px, $py, 0, 0, imagesx($icon), imagesy($icon));
			imagedestroy($icon);
		}
		if ($this->calcSquare($points) <= 4096) goto redraw;

		//$this->preparePopup($this->calcSquare($points), 'info');
		imagepng($im);
		imagedestroy($im);
	}

	public function ajaxCheck() {

		$point = filter_input_array(INPUT_POST, [
			'x' => FILTER_VALIDATE_FLOAT,
			'y' => FILTER_VALIDATE_FLOAT,
		]);

		//$point['game'] = filter_input(INPUT_COOKIE, 'game');

		var_dump($point);
	}

	private function calcSquare($p) {
		// Вычисление площади треугольника по координатам его вершин
		// p[0] = x1; p[1] = y1; p[2] = x2 ...
		// S=0,5*[(x1-x3)(y2-y3)-(x2-x3)(y1-y3)].

		if (count($p) < 6) return 0.0;
		$s = 0.5 * ((($p[0]-$p[4])*($p[3]-$p[5]))-(($p[2]-$p[4])*($p[1]-$p[5])));
		return abs($s);
	}
}