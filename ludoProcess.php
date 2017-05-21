<?php
// main Ludo class
require_once "LudoClass.php";

$ludoObj = new LudoClass();

// ajax api request capture
$post = json_decode($_POST['data']);

if (isset($post->action) && !empty($post->action)) {

    $action = $post->action;

    switch ($action) {
        case 'diceRoll' : diceRollAction($ludoObj);
            break;
        case 'move' : moveAction($post, $ludoObj);
            break;
        case 'refresh' : refreshAction($ludoObj);
            break;
    }
}


/**
 *  this function will randomly generate dice result for specific player
 */
function diceRollAction ($ludoObj) {
    $player = null;

    // who's turn it is
    if (!isset($_SESSION['turn'])) {
        $_SESSION['last_result'] = 0;
        $_SESSION['turn'] = 0;
        $player = $ludoObj->players[$_SESSION['turn']];

    } else {
        if ($_SESSION['turn'] >= 3 && $_SESSION['last_result'] < 6) {
            $player = $ludoObj->players[0];
            $_SESSION['turn'] = 0;

        } elseif ($_SESSION['last_result'] == 6) {
            $player = $ludoObj->players[$_SESSION['turn']];

        } else {
            $_SESSION['turn'] = $_SESSION['turn'] + 1;
            $player = $ludoObj->players[$_SESSION['turn']];

        }

    }

    // random no from 1 to 6
    $result = rand(1, 6);

    $_SESSION['last_result'] = $result;

    echo ucfirst($player) . ' : ' . $result;
}

/**
 * this will check for every move requesting by player for a specific piece
 * @param $post
 * @param $ludoObj
 */
function moveAction ($post, $ludoObj) {

    $boxId = $post->boxId;
    $currentValue = $post->currentValue;

    $boxIdDetails = explode('_', $boxId);

    // check move for which player
    switch ($boxIdDetails[1]) {
        case 'r':
            $player = 0;
            break;
        case 'b':
            $player = 1;
            break;
        case 'y':
            $player = 2;
            break;
        case 'g':
            $player = 3;
            break;
    }

    // checking if this move is for specific player on which it requesting to move for
    if (count($boxIdDetails) > 0 && $boxIdDetails[1] == substr($ludoObj->players[$_SESSION['turn']], 0, 1)) {

        // is it a first move for the home piece
        if ($_SESSION['last_result'] == 6 && $currentValue == '' && (!in_array($boxIdDetails[2], $_SESSION[$ludoObj->players[$player] . 'pieceNotInGame']))) {

            $_SESSION[$ludoObj->players[$player].$boxIdDetails[2].'LatestPosition'] = $_SESSION[$ludoObj->players[$player].'FirstPosition'];

            echo $_SESSION[$ludoObj->players[$player].$boxIdDetails[2].'LatestPosition'];

        // or it is move for a piece which is already out of home
        } elseif (strlen($currentValue) === 4 || $currentValue != '' && (!in_array($boxIdDetails[2], $_SESSION[$ludoObj->players[$player] . 'pieceNotInGame']))) {

            $details = array(
                'player' => $ludoObj->players[$player],
                'piece' => $boxIdDetails[2],
                'latestPosition' => $currentValue
            );

            // call next move function to calculate if not the first move for that piece
            nextMove($details);

            // checking if this piece already not end the game
        } elseif ($currentValue == '' && $_SESSION['last_result'] < 6  && (!in_array($boxIdDetails[2], $_SESSION[$ludoObj->players[$player] . 'pieceNotInGame']))) { // also -1 means already reched end for that piece

            echo 'This piece need 6 to move out from home. Try other piece.';

        // already reached end
        } elseif ($currentValue == -1) { // also -1 means already reched end for that piece
            // remove that piece from game
            $_SESSION[$ludoObj->players[$player] . 'pieceNotInGame'] = array_push($boxIdDetails[2]);

           echo 'This piece already reached to end. Choose another piece for this player.';

        }
    }else{ // this move is not for the specific player requesting for
        echo 'Wrong player trying to move. This is for player '. ucfirst($ludoObj->players[$_SESSION['turn']]);

    }

}

/**
 * this will calculate what will be next position for that piece
 * @param $details
 */
function nextMove ($details) {

    $latestPosition = $details['latestPosition']; // current position for the stick/piece
    $needToMove = $_SESSION['last_result']; // dice roll out result
    $positions = $_SESSION[$details['player'].'PositionsToMove']; // all positions for this player

    $toMove = 0;
    $nowMoveCountStart = 0;
    $reached = 0;

    // loop all positions for this player's specific piece
    foreach ($positions as $position) {

        // where is right now
        if ($position === $latestPosition && $nowMoveCountStart == 0) {
            $nowMoveCountStart = 1;
            $toMove++;

        // reach the point need to move from last point / box
        } elseif ($toMove == $needToMove && $nowMoveCountStart == 1) {
            $reached = 1;
            $_SESSION[$details['player'].$details['piece'].'LatestPosition'] = $position;
            break;

        // not come where is existing place/box
        } elseif($nowMoveCountStart == 1) {
            $toMove++;

        }
    }

    // waiting to end the game but not got exact move to release
    if ($reached === 0) { // not get exact move to reach the end so remain in same place
        $_SESSION[$details['player'].$details['piece'].'LatestPosition'] = $latestPosition;

    }

    echo $_SESSION[$details['player'].$details['piece'].'LatestPosition'];

}

/**
 * refresh the game with destroying all existing sessions
 * @return bool
 */
function refreshAction () {
    session_destroy();

    return true;
}
    

