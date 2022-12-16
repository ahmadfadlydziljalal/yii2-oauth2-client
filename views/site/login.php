<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

/** @see \app\controllers\SiteController::actionLogin() */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\authclient\widgets\AuthChoice;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-12 col-sm-12 col-md-8 col-lg-6">
            <p class="text-muted">Login menggunakan OAuth2 </p>

           <?php $form = ActiveForm::begin([
              'id' => 'login-form',
              'layout' => 'horizontal',
              'fieldConfig' => [
                 'template' => "{label}\n{input}\n{error}",
                 'labelOptions' => ['class' => 'col-lg-1 col-form-label mr-lg-3'],
                 'inputOptions' => ['class' => 'col-lg-3 form-control'],
                 'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],
              ],
           ]); ?>
           <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
           <?= $form->field($model, 'password')->passwordInput() ?>
           <?= $form->field($model, 'rememberMe')->checkbox() ?>
           <?= Html::submitButton('Login', ['class' => 'btn btn-primary mb-3', 'name' => 'login-button']) ?>
           <?php ActiveForm::end(); ?>
        </div>
        <div class="col-12 col-sm-12 col-md-8 col-lg-6">

           <?php $authAuthChoice = AuthChoice::begin([
              'baseAuthUrl' => ['site/authorize'],
              'popupMode' => true,
           ]); ?>

           <?php if ($authAuthChoice->getClients()): ?>
            <div class="text-center">
               <p class="text-muted">Atau login menggunakan account : </p>
               <div class="d-flex justify-content-center gap-5">
                  <?php
                  foreach ($authAuthChoice->getClients() as $client):
                     if (isset($client->getViewOptions()['icon'])) :
                        echo $authAuthChoice->clientLink($client, Html::img($client->getViewOptions()['icon']));
                     else :
                        echo $authAuthChoice->clientLink($client);
                     endif;
                  endforeach;
                  ?>
               </div>
            </div>
           <?php endif; ?>
           <?php AuthChoice::end(); ?>
        </div>
    </div>


</div>