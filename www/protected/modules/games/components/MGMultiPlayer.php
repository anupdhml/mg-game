<?php
/**
 *
 * @package
 */
abstract class MGMultiPlayer extends CComponent
{
    /**
     * @param GameDTO $game
     * @param GameTurnDTO $turn
     * @return null|integer
     * @throws CHttpException
     */
    protected function saveSubmission($game, $turn)
    {
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        if (isset($turn->tags) && is_array($turn->tags) && count($turn->tags) > 0) {
            $submit = new GameSubmission;
            $submit->submission = json_encode($turn->tags);
            $submit->turn = $turn->turn;
            $submit->session_id = (int)Yii::app()->session[$api_id . '_SESSION_ID'];
            $submit->played_game_id = $game->playedGameId;
            $submit->created = date('Y-m-d H:i:s');

            if ($submit->validate()) {
                $submit->save();
                return $submit->id;
            } else {
                throw new CHttpException(500, Yii::t('app', 'Internal Server Error.'));
            }
        }
        return null;
    }


    /**
     * This method get one random media that are available for the user.
     *
     * @param string[] $types image,audio and video
     * @return null|GameMediaDTO
     */
    protected function getMedia($types = array("image"))
    {
        $usedMedias = $this->getUsedMedias();

        if (Yii::app()->user->isGuest) {
            $where = array('and', 'i.locked=1', '(inst.status=1 or i.institution_id is null)', array('not in', 'i.id', $usedMedias));
            if (is_array($types) && count($types) > 0) {
                $where_add = '(';
                $num_types = count($types);
                $i = 0;
                foreach ($types as $mime_type) {
                    if ($i < $num_types && $i > 0) {
                        $where_add .= " or ";
                    }
                    $where_add .= "LEFT( i.mime_type, 5) = '$mime_type'";
                    $i++;
                }
                $where_add .= ')';
                $where[] = $where_add;
            }
            $media = Yii::app()->db->createCommand()
                ->selectDistinct('i.id, i.name, i.mime_type, is.licence_id, (i.last_access IS NULL OR i.last_access <= now()-is.last_access_interval) as last_access_ok,inst.url')
                ->from('{{collection_to_media}} is2i')
                ->join('{{media}} i', 'i.id=is2i.media_id')
                ->join('{{collection}} is', 'is.id=is2i.collection_id')
                ->leftJoin('{{institution}} inst', 'inst.id=i.institution_id')
                ->where($where)
                ->order('RAND()')
                ->limit(1)
                ->queryAll();
        } else {
            // if a player is logged in the medias should be weight by interest
            $where = array('and', '(usm.user_id IS NULL OR usm.user_id=:userID)', 'i.locked=1', '(inst.status=1 or i.institution_id is null)', array('not in', 'i.id', $usedMedias));

            if (is_array($types) && count($types) > 0) {
                $where_add = '(';
                $num_types = count($types);
                $i = 0;
                foreach ($types as $mime_type) {
                    if ($i < $num_types && $i > 0) {
                        $where_add .= " or ";
                    }
                    $where_add .= "LEFT( i.mime_type, 5) = '$mime_type'";
                    $i++;
                }
                $where_add .= ')';
                $where[] = $where_add;
            }
            $media = Yii::app()->db->createCommand()
                ->selectDistinct('i.id, i.name, i.mime_type, is.licence_id, MAX(usm.interest) as max_interest, (i.last_access IS NULL OR i.last_access <= now()-is.last_access_interval) as last_access_ok,inst.url')
                ->from('{{collection_to_media}} is2i')
                ->join('{{media}} i', 'i.id=is2i.media_id')
                ->join('{{collection}} is', 'is.id=is2i.collection_id')
                ->leftJoin('{{collection_to_subject_matter}} is2sm', 'is2sm.collection_id=is2i.collection_id')
                ->leftJoin('{{user_to_subject_matter}} usm', 'usm.subject_matter_id=is2sm.subject_matter_id')
                ->leftJoin('{{institution}} inst', 'inst.id=i.institution_id')
                ->where($where, array(':userID' => Yii::app()->user->id))
                ->group('i.id, i.name, is.licence_id')
                ->order('RAND()')
                ->limit(1)
                ->queryAll();
        }

        if ($media) {
            $mediaDTO = new GameMediaDTO();
            if ($media['url']) {
                $path = $media['url'] . Yii::app()->fbvStorage->get('settings.app_upload_url');
            } else {
                $path = Yii::app()->getBaseUrl(true) . Yii::app()->fbvStorage->get('settings.app_upload_url');
            }

            list($mediaType, $type_2) = explode("/", $media["mime_type"]);

            $mediaDTO->id = $media["id"];
            $mediaDTO->mimeType = $media["mime_type"];
            $mediaDTO->licence = $this->getLicenceInfo($media['licence_id']);

            if ($mediaType === "image") {
                $mediaDTO->thumbnail = $path . "/thumbs/" . $media["name"];
                $mediaDTO->imageFullSize = $path . "/images/" . $media["name"];
                //$scaled = $final_screen = $path . "/images/". urlencode($medias[$i]["name"]);
            } else if ($mediaType === "video") {
                $mediaDTO->thumbnail = $path . "/videos/" . urlencode(substr($media["name"], 0, -4) . "jpeg");
                $mediaDTO->videoWebm = $path . "/videos/" . urlencode($media["name"]);
                $mediaDTO->videoMp4 = $path . "/videos/" . urlencode(substr($media["name"], 0, -4) . "mp4");
            } else if ($mediaType === "audio") {
                $mediaDTO->thumbnail = Yii::app()->getBaseUrl(true) . "/images/audio.png";
                $mediaDTO->audioMp3 = $path . "/audios/" . urlencode($media["name"]);
                $mediaDTO->audioOgg = $path . "/audios/" . urlencode(substr($media["name"], 0, -3) . "ogg");
            }

            return $mediaDTO;
        } else {
            return null;
        }
    }


    /**
     * Returns the full distinct info about licences used on this turn.
     *
     * @param integer $id
     * @return GameLicenceDTO
     */
    protected function getLicenceInfo($id)
    {
        $data = Yii::app()->db->createCommand()
            ->selectDistinct('l.id, l.name, l.description')
            ->from('{{licence}} l')
            ->where('l.id=:id', array(':id' => $id))
            ->queryAll();
        $licence = new GameLicenceDTO();
        $licence->id = $data['id'];
        $licence->name = $data['name'];
        $licence->description = $data['description'];
        return $licence;
    }

    /**
     * Retrieve the IDs of all medias that have been seen/used by the current user
     * on a per game and per session basis.
     *
     * @return integer[] the ids of the medias that have been already seen by the current user in this session
     */
    protected function getUsedMedias()
    {
        $used_medias = array();
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        if (!isset(Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'])) {
            Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'] = array();
        } else {
            $arr_img = Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'];
        }
        return $used_medias;
    }

    /**
     * Add medias to the used medias list stored in the current session for the currently
     * played game
     *
     * @param integer[] $usedMedias the medias that have been shown to the user
     */
    protected function setUsedMedias($usedMedias)
    {
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");

        $arr_img = array();
        if (!isset(Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'])) {
            Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'] = $arr_img;
        } else {
            $arr_img = Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'];
        }
        $arr_img = array_unique(array_merge($arr_img, $usedMedias));

        Media::model()->setLastAccess($usedMedias);

        Yii::app()->session[$api_id . '_GAMES_USED_IMAGES'] = $arr_img;
    }

    /**
     * @param MGGameModel $game
     * @return boolean
     */
    public function registerGamePlayer($game)
    {
        $apiId = Yii::app()->fbvStorage->get("api_id", "MG_API");
        $sessionId = (int)Yii::app()->session[$apiId . '_SESSION_ID'];

        $gamePlayer = new GamePlayer();
        $gamePlayer->session_id = $sessionId;
        $gamePlayer->game_id = $game->id;
        $gamePlayer->created = date('Y-m-d H:i:s');
        $gamePlayer->status = GamePlayer::STATUS_WAIT;

        if ($gamePlayer->save()) {
            return true;
        }

        return false;
    }

    /**
     * Attempts to pair the waiting player with a second one.
     *
     * @param string $username
     * @param MGGameModel $game the game object
     * @param object $game_model the model representing the game in the database
     * @param object $game_engine the game engine of the game
     * @return boolean false if no partner found true if a partner has been found and the played_game is entered into the database
     */
    protected function getPlayer($username, $game)
    {

        $apiId = Yii::app()->fbvStorage->get("api_id", "MG_API");
        $sessionId = (int)Yii::app()->session[$apiId . '_SESSION_ID'];
        $found = false;

        Yii::app()->db->createCommand("LOCK TABLES {{game_player}} READ")->execute();

        $player = null;
        $criteria = new CDbCriteria;
        $criteria->alias = 'gp';
        $criteria->select = 'gp.*';
        if ($username && !empty($username)) {
            $criteria->join = "  LEFT JOIN {{session}} s ON s.id=gp.session_id";
            $criteria->condition('gp.game_id = :gameID AND gp.session_id_1 <> :sessionID AND s.username=:username AND gp.status=:status');
            $criteria->params = array(":gameID" => $game->id, ":sessionID" => $sessionId, ":username" => $username, ":status" => GamePlayer::STATUS_WAIT);
        } else {
            $criteria->condition('gp.game_id = :gameID AND gp.session_id_1 <> :sessionID AND gp.status=:status');
            $criteria->params = array(":gameID" => $game->id, ":sessionID" => $sessionId, ":username" => $username, ":status" => GamePlayer::STATUS_WAIT);
        }
        $player = GamePlayer::model()->find($criteria);
        $paired = false;
        if ($player) {
            $player->status = GamePlayer::STATUS_PAIR;
            if ($player->save()) {
                //todo: send push notification
                $currentPlayer = GamePlayer::model()->find('session_od =:sessionId AND remote_id=:remoteID', array(':sessionId' => $sessionId));
                $currentPlayer->status = GamePlayer::STATUS_PAIR;
                if ($currentPlayer->save()) {
                    $paired = true;
                }
            }
        }
        Yii::app()->db->createCommand("UNLOCK TABLES")->execute();
        return $paired;
    }
}