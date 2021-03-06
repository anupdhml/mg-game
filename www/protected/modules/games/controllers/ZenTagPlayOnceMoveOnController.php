<?php

Yii::import("games.controllers.ZenTagController");
class ZenTagPlayOnceMoveOnController extends ZenTagController 
{
  
  public function filters() {
    return array(
      'IPBlock',
      'accessControl - index', 
      );
  }
  
  public function accessRules() {
    return array(
        array('allow', 
          'actions'=>array('index'),
          'users'=>array('*'),
          ),
        array('allow',
            'actions' => array('update'),
            'roles' => array(ADMIN),
        ),
        array('allow',
            'actions' => array('view'),
            'roles' => array(EDITOR),
        ),
        array('deny', 
          'users'=>array('*'),
          ),
        );
  }
  
  /**
   * As most of the game play is handled via JavaScript and API callbacks the controller
   * renders only the initial needed HTML while making sure all needed assets CSS 
   * and JavaScript are loaded 
   */
  public function actionIndex() {
    MGHelper::setFrontendTheme();
    
    $game = GamesModule::loadGame("ZenTagPlayOnceMoveOn");
    if ($game) {
      $cs = Yii::app()->clientScript;
      $cs->registerCoreScript('jquery');
      $cs->registerCssFile(Yii::app()->baseUrl . '/css/jquery.fancybox-1.3.4.css');
      $cs->registerCssFile(GamesModule::getAssetsUrl() . '/zentag/css/style.css');
      $cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.fancybox-1.3.4.pack.js', CClientScript::POS_END);
      $cs->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.tmpl.min.js', CClientScript::POS_END);
      $cs->registerScriptFile(Yii::app()->baseUrl . '/js/mg.api.js', CClientScript::POS_END);
      $cs->registerScriptFile(Yii::app()->baseUrl . '/js/mg.game.api.js', CClientScript::POS_END);
      // We now have a separate javascript file for
      // ZenTagPlayOnceMoveOn, so we aren't going to use ZenTag's js
      // file directly.
      //$cs->registerScriptFile(GamesModule::getAssetsUrl() . '/zentag/js/mg.game.zentag.js', CClientScript::POS_END);
      $cs->registerScriptFile(GamesModule::getAssetsUrl() .
			      '/zentagplayoncemoveon/js/mg.game.zentagplayoncemoveon.js',
			      CClientScript::POS_END);

      $throttleInterval = (int)Yii::app()->fbvStorage->get("settings.throttle_interval", 1500);
      $asset_url = Yii::app()->baseUrl;
       
      $js = <<<EOD
    MG_GAME_ZENTAG_PLAYONCE.init({
        gid : 'ZenTagPlayOnceMoveOn',
        app_id : 'MG_API',
        asset_url : '$asset_url',
        api_url : '{$game->api_base_url}',
        game_base_url : '{$game->game_base_url}',
        msg_url : '{$game->base_url}/mg_api_messages.php',
        play_once_and_move_on : {$game->play_once_and_move_on},
        play_once_and_move_on_url : '{$game->play_once_and_move_on_url}',
        throttleInterval : $throttleInterval
      });
EOD;
      Yii::app()->clientScript->registerScript(__CLASS__.'#game', $js, CClientScript::POS_READY);
      
      if ($game->play_once_and_move_on == 1) {
        $this->layout = '//layouts/main_no_menu';
      } else {
        $this->layout = '//layouts/column1';
      }
      $this->render('/zenTag/index', array(
        'game' => $game,
      ));  
    } else {
      throw new CHttpException(403, Yii::t('app', 'The game is not active.'));
    }
  }
  
  /**
   * show the game's settings
   */
  public function actionView() {
    $model = $this->loadModel(array("unique_id" => "ZenTagPlayOnceMoveOn"), 'ZenTagPlayOnceMoveOn');  
    $model->fbvLoad();
    
    $this->render('/zenTag/view', array(
      'model' => $model,
      'statistics' => GamesModule::getStatistics($model->id)
    ));
  }
  
  /**
   * edit the game's settings
   */
  public function actionUpdate() {
    $model = $this->loadModel(array("unique_id" => "ZenTagPlayOnceMoveOn"), 'ZenTagPlayOnceMoveOn');
    $model->fbvLoad();
    
    $this->performAjaxValidation($model, 'zentag-form');
    if (isset($_POST['ZenTagPlayOnceMoveOn'])) {
      $model->setAttributes($_POST['ZenTagPlayOnceMoveOn']);
      
      $relatedData = array(
        'collections' => $_POST['ZenTagPlayOnceMoveOn']['collections'] === '' ? null : $_POST['ZenTagPlayOnceMoveOn']['collections'],
        'plugins' => $_POST['ZenTagPlayOnceMoveOn']['plugins'] === '' ? null : $_POST['ZenTagPlayOnceMoveOn']['plugins'],
        );
      
      // save the games data in the database
      if ($model->saveWithRelated($relatedData)) {
        $model->fbvSave(); // but also save it in the settings file as each game uses FBVstorage
        
        MGHelper::log('update', 'Game ' . $model->name . ' updated');
        Flash::add('success', $model->name . ' ' . Yii::t('app', "Updated"));
        $this->redirect(array('view'));
      }
    }
    
    $this->render('/zenTag/update', array(
      'model' => $model,
      ));
  }
}