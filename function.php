<?php
session_start();


function game_setName()
{
	// $this_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	// //الان میشه://localhost:808/projects/tic-tac-toe/?action=setName
	// $this_url = strtok($this_url, "?");
	// // هم میشه //localhost:808/projects/tic-tac-toe/
	// define('CURRENT_URL', $this_url);
	// defined in: fanction geme()
	$el = null;
	$el .= "<form method='post' id='game' class='form'>";
	$el .=  "<br />	<input  type = 'text' name= 'player1' value= '" . $_SESSION['playerNames']['O'] . " ' placeholder = 'player1' />";
	$el .= "<input  type = 'text' name= 'player2' value= '" . $_SESSION['playerNames']['X'] . " ' placeholder = 'player2' />";
	$el .= "<br /> <input class='button'  type = 'submit' name ='setName' value = 'save Name'/>";
	$el .= "<br />	<a href='" . CURRENT_URL . "'>Return</a>";
	$el  .= "<form>";
	if (isset($_POST['setName'])) {
		$p1 = ucwords($_POST['player1']);
		$p2 = ucwords($_POST['player2']);
		$_SESSION['playerNames'] = ['O' => $p1, 'X' => $p2];
		$_SESSION['last_winner'] = null;
		header("location:" . CURRENT_URL);
	}
	return $el;
}



//  تابع آرایه با تابع میتونه کار رسم جدول رو دکدفعه انجام بده.
function game_start($_lastGame = null)
{
	$_SESSION['status'] = 'awaiting';
	$_SESSION['save'] = false;
	// اگر وسط بازی مرورگر بسته شد
	if ($_lastGame && is_array($_lastGame)) {
		$_SESSION['game'] = $_lastGame['game'];
		$_SESSION['playerNames'] = $_lastGame['playerNames'];
		$_SESSION['last_winner'] = $_lastGame['last_winner'];
		$_SESSION['game_move_x'] = $_lastGame['game_move_x'];
		$_SESSION['game_move_o'] = $_lastGame['game_move_o'];
		$_SESSION['status'] = $_lastGame['status'];
		$_SESSION['for_optimization'] = $_lastGame['for_optimization'];
		$_SESSION['current'] = $_lastGame['current'];
	} else {
		$_SESSION['game'] =	[
			1 => null,
			2 => null,
			3 => null,
			4 => null,
			5 => null,
			6 => null,
			7 => null,
			8 => null,
			9 => null,
		];
		$_SESSION['game_move_x'] = 0;
		$_SESSION['game_move_o'] = 0;
		if (!isset($_SESSION['playerNames'])) {
			$_SESSION['playerNames'] = ['O' => 'Player1', 'X' => 'Player2'];
		}
		if (isset($_SESSION['last_winner'])) {
			$_SESSION['current']	=	 $_SESSION['last_winner'];
		} else {
			$randPlayer = array_rand($_SESSION['playerNames'], 1);
			$_SESSION['current'] = $randPlayer;
			// خط پایین برای این هست که در مراحل بعد میخواستیم لست وینر رو پرش کنیم خطای نعریف نشده نده
			$_SESSION['last_winner'] = null;
			// خط پایین برای این هست که به خودمون نشون بده که این رندم داره کار میکنه.
			var_dump($_SESSION['current']);
		}
		$_SESSION['game_time_start'] = time();
	}
}



function game_playerHistory($_needle)
{
	$cookieName = 'game_detail_' . str_ireplace(' ', '', $_SESSION['playerNames']['X']) . '-' .
		str_ireplace(' ', '', $_SESSION['playerNames']['O']);
	$history = null;
	if (isset($_COOKIE[$cookieName])) {
		$history = $_COOKIE[$cookieName];
		$history = json_decode($history, true);
		// var_dump($history);
	}
	if (isset($history[$_needle])) {
		$history = $history[$_needle];
	} else {
		$history = '-';
	}
	return $history;
}



function game_activeChecker($_player)
{
	if ($_SESSION['current'] === $_player) {
		return ' active';
	}
	// return null;
}



function game_restartBtn()
{
	$result_restartBtn = null;
	$resetValue = "Start";
	$resetName = 'restart';
	if (isset($_SESSION['for_optimization'])) {
		if ($_SESSION['status'] == 'inprogress') {
			$resetValue = 'Resign';
			$resetName = 'resign';
		} elseif ($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw') {
			$resetValue = 'Play again';
		}
		if ($_SESSION['status'] != 'awaiting') {
			$result_restartBtn = " <input type='submit' name='$resetName' value='$resetValue'  id= 'resetBtn' > ";
		}
	}
	return $result_restartBtn;
}
// var_dump($_SESSION['status']);



function game_createTable()
{
	$element = null;
	$element .= " <div class='row title'>";
	$element .= " <div class='row'>";
	if ($_SESSION['status'] === 'awaiting') {
		$element  .= "<a href='?action=setName'>Lets play with your name</a>";
	} else {
		// $history = game_playerHistory('draw');
		// var_dump($history);
		$lastWinner = ['X' => null, 'O' => null];
		$cup = '<i class="fas fa-trophy"></i> <i class="fa fa-trophy" aria-hidden="true"> </i> ';
		// if ($_SESSION['last_winner'] === 'O') {
		// 	$lastWinner['O'] = $cup;
		// } elseif ($_SESSION['last_winner'] === 'X') {
		// 	$lastWinner['X'] = $cup;
		// }
		if (game_playerHistory('win_X') > game_playerHistory('win_O')) {
			$lastWinner['X'] = $cup;
		} elseif (game_playerHistory('win_X') < game_playerHistory('win_O')) {
			$lastWinner['O'] = $cup;
		}
		$element .= "<div class ='span1 " . game_activeChecker('O') . "'>
						" . $lastWinner['O'] . $_SESSION['playerNames']['O'] . "
						(<span class = 'cO' >O</span>)<br>
						<div class = 'span5'>
							" . game_playerHistory('win_O') . "
						</div>
					</div>";
		$element .= "	<div class = 'span5'>
							Ties <br />
							<div class = 'span5'>
							" . game_playerHistory('draw') . "
							</div>
						</div>";
		$element .= "<div class = 'span2" . game_activeChecker('X') . "'>
						" . $_SESSION['playerNames']['X'] . "
						(<span class = 'cX' >X</span>)" . $lastWinner['X'] . "<br>
						<div class = 'span5'>
										" . game_playerHistory('win_X') . "
						</div>
					</div>";
	}
	$element .= "</div>";
	// close main row
	$element .= "</div>";
	$element .= "<form method='post' id='game' class='form'>";
	foreach ($_SESSION['game'] as $cell => $value) {
		$classname = null;
		if ($value) {
			$classname = 'c' . $value;
		}
		$element .=  "    <input type='submit' value='$value' class='cell $classname' name= 'cell$cell' ";
		if ($value || $_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw') {
			$element .= " disabled";
		}
		// این برای وقتی هست که توی بروزر راست کلیک و سورس رو میزنیم این قسمت رو به تمیز و هر ردیف رو به صورت جداگانه نشون بده
		$element .= ">\n";
	}
	$element .= game_restartBtn();
	if ($_SESSION['status'] !== 'inprogress') {
		$element .= "</br><a href= '" . CURRENT_URL . "?action=showResult'>Show Result</a>";
		if ($_SESSION['playerNames']['X'] === 'Computer') {
			$element .= "</br><a href= '" . CURRENT_URL . "?action=Player2Player'>Play with player2</a>";
		} else {
			$element .= "</br><a href= '" . CURRENT_URL . "?action=ComputerPlayer'>Play with computer</a>";
		}
	}
	$element .= "</form>";
	return  $element;
}



function game_checkwinner()
{
	if ($_SESSION['status'] == 'awaiting') {
		return null;
	}
	$g = $_SESSION['game'];
	$winner = null;
	if (
		// اولین موردش برای این هست که وقتی همشون خالی بودن،حالت برنده ایجاد نشه
		($g[1] && $g[1] == $g[2] &&	$g[2] == $g[3]) //row1
		||  ($g[4] && $g[4] == $g[5] && $g[5] == $g[6]) //row2
		||  ($g[7] && $g[7] == $g[8] && $g[8] == $g[9]) //row3

		||  ($g[1] && $g[1] == $g[4] && $g[4] == $g[7]) //col
		||  ($g[2] && $g[2] == $g[5] && $g[5] == $g[8]) //col2
		||  ($g[3] && $g[3] == $g[6] && $g[6] == $g[9]) //col3

		||  ($g[1] && $g[1] == $g[5] && $g[5] == $g[9]) // /
		||  ($g[3] && $g[3] == $g[5] && $g[5] == $g[7]) //  \
	) {
		if ($_SESSION['current'] == 'X') {
			$winner = 'O';
		} else {
			$winner = 'X';
		}
	} elseif (!in_array(null, $g)) {
		$winner = false;
	}
	return $winner;
}



function game_saveCookie($_cookieName, $_value = null)
{
	// run json encode
	$_value = json_encode($_value);
	// save cookie
	setcookie($_cookieName, $_value,		time() + (86400 * 365));
}



function game_save()
{
	if (isset($_SESSION['save']) && $_SESSION['save'] === 'true') {
		return false;
	}
	$_SESSION['game_time_end'] = time();
	// وقتی صفحه رو رفرش میشه،		خودبخود به امتیاز ها اضافه نشه
	$_SESSION['save'] = 'true';
	//برای این که وقتی یک دور بازی انجام شد، دوباره نپرسه میخوای ادامه ی قبلی ناتموم رو بازی کنی؟
	game_saveCookie('game_save');
	// save game total result
	game_save_result();
	// save for each players
	game_save_result(str_ireplace(' ', '', $_SESSION['playerNames']['O']), true);
	game_save_result(str_ireplace(' ', '', $_SESSION['playerNames']['X']), true);
	// //save for two players
	game_save_result(str_ireplace(' ', '', $_SESSION['playerNames']['X'] . '-' . $_SESSION['playerNames']['O']));
}



function game_save_result($_player = null, $_single = false)
{
	$_cookiePrefix = 'game_detail';
	if ($_player) {
		$_cookiePrefix .=  '_' . $_player;
	}
	$new_value = [];
	$detail_list = ['count',  'win', 'lose',  'draw', 'resign', 'inprogress', 'total_time', 'total_move', 'total_moves_to_win'];
	// read cookie if exist
	if (isset($_COOKIE[$_cookiePrefix])) {
		$new_value = json_decode($_COOKIE[$_cookiePrefix], true);
	}
	$new_value['player'] = $_player;
	// if cookie is not exist save zero as default value
	foreach ($detail_list as $value) {
		if (!isset($new_value[$value])) {
			$new_value[$value] = 0;
		}
	}
	// var_dump($detail_list);
	// var_dump($new_value['player']);
	// increase count
	$new_value['count'] = $new_value['count'] + 1;
	$game_has_winner = game_checkwinner();
	// if has winner
	if ($game_has_winner) {
		// if this player is winner
		if (str_ireplace(' ', '', $_SESSION['playerNames'][$game_has_winner]) == $_player) {
			$new_value['win'] = $new_value['win'] + 1;
			// save total total_moves_to_win
			$new_value['total_moves_to_win'] = $new_value['total_moves_to_win'] +  $_SESSION['game_move_' . strtolower($game_has_winner)];
			// var_dump($_SESSION['playerNames'][$game_has_winner]);
		}
		// else if this player is looser
		elseif ($_single) {
			$new_value['lose'] = $new_value['lose'] + 1;
		}
		// else for two players together and total
		elseif ($_single === false) {
			if (!isset($new_value['win_' . $game_has_winner])) {
				$new_value['win_' . $game_has_winner] = 0;
			}
			$new_value['win_' . $game_has_winner] = $new_value['win_' . $game_has_winner] + 1;
			$new_value['total_moves_to_win'] = $new_value['total_moves_to_win'] +  $_SESSION['game_move_' . strtolower($game_has_winner)];
			$new_value['win'] = $new_value['win_X'] + $new_value['win_O'];
			unset($new_value['lose']);
			// var_dump($new_value['win']);
		}
		// var_dump($new_value);
		// var_dump($game_has_winner);
	}
	// else if draw
	elseif ($game_has_winner === false) {
		$new_value['draw'] = $new_value['draw'] + 1;
	} elseif (isset($_SESSION['status']) && $_SESSION['status'] === 'resign') {
		if ($_single == false && $_SESSION['current'] == 'X') {
			if (!isset($new_value['win_O'])) {
				$new_value['win_O'] = 0;
			}
			$new_value['win_O'] = $new_value['win_O'] + 1;
			$new_value['resign'] = $new_value['resign'] + 1;
			$new_value['total_moves_to_win'] = $new_value['total_moves_to_win'] +  $_SESSION['game_move_o'];
			$new_value['win'] = $new_value['win_X'] + $new_value['win_O'];
			var_dump($new_value['win_X']);
		} elseif ($_single == false && $_SESSION['current'] == 'O') {
			if (!isset($new_value['win_O'])) {
				$new_value['win_X'] = 0;
			}
			$new_value['win_X'] = $new_value['win_X'] + 1;
			$new_value['resign'] = $new_value['resign'] + 1;
			$new_value['total_moves_to_win'] = $new_value['total_moves_to_win'] +  $_SESSION['game_move_x'];
			$new_value['win'] = $new_value['win_X'] + $new_value['win_O'];
			var_dump($new_value['win_O']);
		} elseif ($_single && str_ireplace(' ', '', $_SESSION['playerNames'][$_SESSION['current']]) == $_player) {
			$new_value['resign'] = $new_value['resign'] + 1;
		} else {
			$new_value['win'] = $new_value['win'] + 1;
			if ($_SESSION['current'] == 'X') {
				$new_value['total_moves_to_win'] = $new_value['total_moves_to_win'] +  $_SESSION['game_move_o'];
			} else {
				$new_value['total_moves_to_win'] = $new_value['total_moves_to_win'] +  $_SESSION['game_move_x'];
			}
			var_dump($new_value['total_moves_to_win']);
		}
	}
	// save total time
	$new_value['total_time'] = $new_value['total_time'] + ($_SESSION['game_time_end'] - $_SESSION['game_time_start']);
	// save total total_move
	$new_value['total_move'] = $new_value['total_move'] +  ($_SESSION['game_move_x'] + $_SESSION['game_move_o']);
	// if save for single player
	if ($_single) {
		$new_value['type'] = 'single';
	}
	// if save for two player
	elseif ($_player) {
		$new_value['type'] = 'against';
	}
	// if save total
	else {
		$new_value['type'] = 'total';
	}
	// if ($new_value['player'] === null)
	// {
	// 				unset($new_value['player']);
	// }
	// save result
	game_saveCookie($_cookiePrefix, $new_value);
	// var_dump($new_value);
	// var_dump($_COOKIE);
	// var_dump($naw_value['total_moves_to_win']);
}



function game_winner()
{
	$game_result =  game_checkwinner();
	$result = false;
	$el_changeName = "<p><a href='?action=setName'>Do you want to save your name?</a></p>";
	if ($game_result) {
		$_SESSION['last_winner'] = $game_result;
		$_SESSION['status'] = 'win';
		$result =  "	<div id= 'result'>" . $_SESSION['playerNames'][$game_result] . " win!$el_changeName </div> ";
		game_save();
	}
	// پایین میگه همه ی خونه ها رو بررسی کن اگه توشون نال هیچی ندیدی، اعلام تساوی کن
	elseif ($game_result === false) {
		// draw
		$_SESSION['last_winner'] = null;
		$_SESSION['status'] = 'draw';
		$result = "<div id='result'> Draw$el_changeName</div>";
		game_save();
	}
	return $result;
}



// این پایین میگه روی هرکدوم که کلیک کردیم،		توی اون خونه رو با توجه به نوبت پر کن
function game_turn()
{
	if ($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw') {
		return null;
	}
	foreach ($_SESSION['game'] as $cell => $value) {
		if (isset($_POST['cell' . $cell])) {
			$_SESSION['status'] = 'inprogress';
			$_SESSION['for_optimization'] = 'start';
			$_SESSION['game'][$cell] = $_SESSION['current'];
			if ($_SESSION['current'] === 'X') {
				$_SESSION['current'] = 'O';
				$_SESSION['game_move_x'] = $_SESSION['game_move_x'] + 1;
			} else {
				$_SESSION['current'] = 'X';
				$_SESSION['game_move_o'] = $_SESSION['game_move_o'] + 1;
			}
		}
	}
	if ($_SESSION['status'] === 'inprogress') {
		$game_save_array = [
			'game' => $_SESSION['game'],
			'playerNames' => $_SESSION['playerNames'],
			'game_move_x' => $_SESSION['game_move_x'],
			'game_move_o' => $_SESSION['game_move_o'],
			'last_winner' => $_SESSION['last_winner'],
			'status' => $_SESSION['status'],
			'for_optimization' => $_SESSION['for_optimization'],
			'current' => $_SESSION['current']
		];
		game_saveCookie('game_save', $game_save_array);
	}
}



function game_sortResult($_datatable, $_by = null, $_desc = null)
{
	// get input value from user in get
	if ($_by === null) {
		if (isset($_GET['by'])) {
			$_by = $_GET['by'];
		} else {
			$_by = 'point';
		}
	}
	// get input value from user in get
	if ($_desc === null) {
		if (isset($_GET['asc'])) {
			$_desc = false;
		} else {
			$_desc = true;
		}
	}
	// settig array(player => $_by)
	$datatable_filtered = array_column($_datatable, $_by, 'player');
	// removing zero or null content
	$datatable_filtered = array_filter($datatable_filtered);
	if ($_desc) {
		// sort array descending
		arsort($datatable_filtered);
	} else {
		// sort array ascending
		asort($datatable_filtered);
	}
	// append the different , overwrite the rqual    content
	$_datatable = array_merge($datatable_filtered, $_datatable);
	return $_datatable;
}



function game_updatePoint($_datatable, $_field, $_desc)
{
	$_datatable = game_sortResult($_datatable, $_field, $_desc);
	$datatable_filtered = array_column($_datatable, $_field, 'player');
	$counter =  0;
	foreach ($datatable_filtered as $playerName => $value) {
		if ($value && $counter = 0) {
			// Improve ine level
			// by decreasing number
			$_datatable[$playerName]['point'] = $_datatable[$playerName]['point'] + 3;
		} elseif ($value && $counter = 1) {
			// Improve ine level
			// by decreasing number
			$_datatable[$playerName]['point'] = $_datatable[$playerName]['point'] + 2;
		} elseif ($value && $counter = 2) {
			// Improve ine level
			// by decreasing number
			$_datatable[$playerName]['point'] = $_datatable[$playerName]['point'] + 1;
		}
		$counter++;
	}
	return $_datatable;
}



/*
calculate rank of player
Gold 1
Silver 2
Bronze 3
*/
function game_getRank($_datatable)
{
	$_datatable = game_sortResult($_datatable, 'point', true);
	$datatable_filtered = array_column($_datatable, 'point', 'player');
	$counter = 0;
	// $silver = null;
	// $bronze = null;
	foreach ($datatable_filtered as $playerName => $point) {
		// var_dump($_datatable[$playerName]['player']);
		if ($_datatable[$playerName]['win'] > 0 && $playerName != 'total') {
			// var_dump($_datatable[$playerName]['win']);
			if ($counter == 0) {
				// GOLD
				$_datatable[$playerName]['rank'] = 1;
				// $gold = true;
			}
			if ($counter == 1) {
				// SILVER
				$_datatable[$playerName]['rank'] = 2;
				// $silver = true;
			}
			if ($counter == 2) {
				// BRONZE
				$_datatable[$playerName]['rank'] = 3;
				// $bronze = true;
			}
			$counter++;
			// var_dump($_datatable[$playerName]['rank']);

		} else {
			$_datatable[$playerName]['rank'] = 4;
		}
	}
	// false=> ascendig
	// $_datatable = game_updateRank($_datatable, 'avg_time', false);
	// $_datatable = game_updateRank($_datatable, 'total_moves_to_win', false);
	// $_datatable = game_updateRank($_datatable, 'avh_time_move', false);
	return $_datatable;
}



function game_getResult($_type = 'single')
{
	$result = null;
	// if user want to custom type show it, first click on it's link
	if (isset($_GET['type'])) {
		$_type = $_GET['type'];
	}
	foreach ($_COOKIE as $key => $value) {
		if (strpos($key, 'game_detail') !== false) {
			// decode cookie value
			$value = json_decode($value, true);
			if (isset($value['type']) && $value['type'] == $_type) {
				// set for lose
				if ($value['type'] == 'single') {
					$lose = $value['lose'];
				} else {
					$lose = '-';
				}
				// if player name is not set use total
				if (!isset($value['player'])) {
					$value['player'] = 'Total';
				}
				$point = $value['win'] * 3 + $value['draw'] * 1;
				$avg_time_game =  round($value['total_time'] / $value['count'], 2);
				$avg_time_move = round($value['total_time'] / $value['total_move'], 2);
				$avg_moves_to_win = '-';
				if ($value['win'] > 0) {
					$avg_moves_to_win = round($value['total_moves_to_win'] / $value['win'], 2);
					// var_dump($value['total_moves_to_win']);
				}
				// var_dump($avg_moves_to_win);
				$result[$value['player']] =
					[
						'player' => $value['player'],
						'count' => $value['count'],
						'win' => $value['win'],
						'lose' => $lose,
						'draw' => $value['draw'],
						'resign' => $value['resign'],
						'inprogress' => $value['inprogress'],
						'avg_time_game' => $avg_time_game,
						'avg_moves_to_win' =>	$avg_moves_to_win,
						'avg_time_move' => $avg_time_move,
						'point' => $point,
						'rank' => null,
					];
			}
		}
	}

	$result = game_updatePoint($result, 'avg_moves_to_win', false);
	$result = game_getRank($result);
	$result = game_sortResult($result);
	// var_dump($value);
	// var_dump($result);
	return $result;
}



function game_getRankName($_rank)
{
	if ($_rank > 3) {
		$_rank = '-';
	} elseif ($_rank == 1) {
		$_rank = 'Gold';
	} elseif ($_rank == 2) {
		$_rank = 'Silver';
	} elseif ($_rank == 3) {
		$_rank = 'Bronze';
	}
	return $_rank;
	// switch($_rank)
	// {
	// 				case 1:
	// 								$_rank = 'Gold';
	// 								break;
	// 				case 2:
	// 								$_rank = 'Silver';
	// 								break;
	// 				case 3:
	// 								$_rank = 'Bronze';
	// 								break;
	// 				default:
	// 								$_rank = '-';
	// 								break;
	// }
}



function game_showResult($_type = 'single')
{
	// var_dump($_COOKIE);
	// get game result
	$datatable = game_getResult();
	if (!$datatable) {
		return null;
	}
	$field = $datatable[key($datatable)];
	$field = array_keys($field);
	$result = null;
	$result .= '<ol id="resultTable">';
	$result .= '<div class="ttitle">';
	$ascParam = null;
	// if user want custom type show
	if (!isset($_GET['asc'])) {
		$ascParam = '&asc=true';
	}
	// draw table title
	foreach ($field as $key => $fieldName) {
		$changedNames = str_ireplace('_', " ", $fieldName);
		$titleNames = ucwords($changedNames);
		$result .= "<span class='f_$fieldName'><a href='?action=showResult&by=$fieldName$ascParam' >$titleNames</a></span>";
	}
	$result .= '</div>';
	// draw table data
	foreach ($datatable as $playerName  => $value) {
		$result .= '<li>';
		foreach ($field as $key => $fieldName) {
			if ($fieldName === 'rank') {
				$value[$fieldName] = game_getRankName($value[$fieldName]);
			}
			$result .= "<span class='f_$fieldName'>" . $value[$fieldName] . '</span>';
		}
		$result .= '</li><br />';
	}
	$result .= '</ol>';
	$result .= "<div class ='Table-row'>";
	$result .= "<a href='" . CURRENT_URL . "?action=showResult&type=total'>Total</a> | ";
	$result .= "<a href='" . CURRENT_URL . "?action=showResult&type=single'>Single</a> | ";
	$result .= "<a href='" . CURRENT_URL . "?action=showResult&type=against'>Against</a> ";
	$result .= " <br /><br />		<a href='" . CURRENT_URL . "'>Return</a>";
	$result .= "</div>";
	// var_dump($_COOKIE);
	return $result;
}



function game_computerMove()
{
	$emptyCells = null;
	foreach ($_SESSION['game'] as $cell => $value) {
		if (!$value) {
			$emptyCells[] = $cell;
		}
	}
	// echo $computerMove;
	//   random selection key
	$computerMove = array_rand($emptyCells, 1);
	$computerMove = $emptyCells[$computerMove];
	return $computerMove;
}



function game()
{
	// خط بعد با توجه به صفحه مثلا ممکنه باشه://localhost:808/projects/tic-tac-toe/?action=setName
	$this_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	// خط پایین هم میشه؟ //localhost:808/projects/tic-tac-toe/
	$this_url = strtok($this_url, "?");
	define('CURRENT_URL', $this_url);
	// var_dump($_SERVER[REQUEST_URI]);
	$el = null;
	// user press start btn OR start the game at the first
	if (!isset($_SESSION['status'])) {
		if (isset($_COOKIE['game_save'])) {
			$el .= "<a href = '" . CURRENT_URL . "?action=new'>Do you want to play new game?</a>";
			$lastGame = json_decode($_COOKIE['game_save'], true);
			game_start($lastGame);
			// var_dump($_COOKIE['game_save']);
		} else {
			game_start();
		}
	}
	// elseif user prss ttart restart btn
	elseif (isset($_POST['restart'])) {
		game_save();
		game_start();
	}
	//user press resign btn
	elseif (isset($_POST['resign'])) {
		$_SESSION['status'] = 'resign';
		game_save();
		game_start();
	} else {
		game_turn();
	}
	if (isset($_GET['action']) && $_GET['action'] == 'setName') {
		$el .= game_setName();
		// var_dump($_SESSION['playerNames']);
	} elseif (isset($_GET['action']) && $_GET['action'] == 'showResult') {
		$el .= game_showResult();
	}
	// play with computer
	elseif (isset($_GET['action']) && $_GET['action'] == 'ComputerPlayer') {
		$_SESSION['playerNames']['X'] = 'Computer';
		$_SESSION['last_winner'] = null;
		$_SESSION['status'] = 'new';
		game_start();
		header("Location:" . CURRENT_URL);
	}
	// play with player2
	elseif (isset($_GET['action']) && $_GET['action'] == 'Player2Player') {
		$_SESSION['playerNames']['X'] = 'Player2';
		$_SESSION['last_winner'] = null;
		$_SESSION['status'] = 'new';
		game_start();
		header("Location:" . CURRENT_URL);
	} elseif (isset($_GET['action']) && $_GET['action'] == 'new') {
		$_SESSION['status'] = 'new';
		game_save();
		game_start();
		header("Location:" . CURRENT_URL);
	} else {
		if (isset($_SESSION['status'])) {
			$el .=  game_winner();
		}
		if ($_SESSION['playerNames']['X'] === 'Computer' && $_SESSION['current'] === 'X' && $_SESSION['status'] != 'win' && $_SESSION['status'] != 'draw') {
			$_SESSION['game'][game_computerMove()] = $_SESSION['current'];
			$_SESSION['current'] = 'O';
			header("Location: " . CURRENT_URL);
		}
		$el .= game_createTable();
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		header("Location:" . CURRENT_URL);
	}
	return $el;
}











//
// function game_checkNameChanged()
// {
//  				if ($_SESSION['playerNames']['x'] !== 'Player1' || $_SESSION['playerNames']['o'] !== 'Player2')
// 				{
//  								return true;
//  				}
// 				return false;
// }
