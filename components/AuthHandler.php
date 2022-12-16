<?php

namespace app\components;

use app\models\Auth;
use app\models\User;
use Exception;
use Yii;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\helpers\VarDumper;

/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class AuthHandler
{

    private ClientInterface $client;
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

   /**
    * @return void
    * @throws \yii\base\Exception
    * @throws \yii\db\Exception
    * @throws Exception
    */
   public function handle()
    {

       Yii::$app->user->returnUrl = Url::to(['index']);

       $attributes = $this->client->getUserAttributes();
       $email = ArrayHelper::getValue($attributes, 'user.email');
       $id = ArrayHelper::getValue($attributes, 'user.id');
       $nickname = ArrayHelper::getValue($attributes, 'user.username');

       /* @var Auth $auth */
       $auth = Auth::find()->where([
          'source' => $this->client->getId(),
          'source_id' => $id,
       ])->one();

       if (Yii::$app->user->isGuest) {

          if ($auth) { // login

             $user = $auth->user;
             $this->updateUserInfo($user);
             Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
             Yii::$app->getSession()->setFlash('success',
                'Login by ' . ucfirst($this->client->getId()) . ' berhasil dan Selamat Datang...! '
             );

          } else { // signup

             if ($email !== null && User::find()->where(['email' => $email])->exists()) {

                Yii::$app->getSession()->setFlash('error', [
                   Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $this->client->getTitle()]),
                ]);

             } else {

                $password = Yii::$app->security->generateRandomString(Yii::$app->params['user.passwordMinLength']);
                $user = new User([
                   'username' => $nickname,
                   //'github' => $nickname,
                   'email' => $email,
                   'password' => $password,
                ]);
                $user->generateAuthKey();
                $user->generatePasswordResetToken();

                $transaction = User::getDb()->beginTransaction();

                if ($user->save()) {

                   $auth = new Auth([
                      'user_id' => $user->id,
                      'source' => $this->client->getId(),
                      'source_id' => (string)$id,
                   ]);

                   if ($auth->save()) {

                      $transaction->commit();

                      Yii::$app->getSession()->setFlash('success',
                         'Anda sudah bergabung via ' . ucfirst($this->client->getId()) . ', dan Selamat Datang...! '
                      );
                      Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);

                   } else {

                      Yii::$app->getSession()->setFlash('error', [
                         Yii::t('app', 'Unable to save {client} account: {errors}', [
                            'client' => $this->client->getTitle(),
                            'errors' =>  yii\helpers\Json::encode($auth->getErrors()) ,
                         ]),
                      ]);
                   }
                } else {

                   Yii::$app->getSession()->setFlash('error', [
                      Yii::t('app', 'Unable to save user: {errors}', [
                         'client' => $this->client->getTitle(),
                         'errors' => yii\helpers\Json::encode($user->getErrors()),
                      ]),
                   ]);

                }
             }
          }
       } else { // user already logged in
          if (!$auth) { // add auth provider

             $auth = new Auth([
                'user_id' => Yii::$app->user->id,
                'source' => $this->client->getId(),
                'source_id' => (string)$attributes['id'],
             ]);

             if ($auth->save()) {

                $user = $auth->user;
                $this->updateUserInfo($user);
                Yii::$app->getSession()->setFlash('success', [
                   Yii::t('app', 'Linked {client} account.', [
                      'client' => $this->client->getTitle()
                   ]),
                ]);

             } else {

                Yii::$app->getSession()->setFlash('error', [
                   Yii::t('app', 'Unable to link {client} account: {errors}', [
                      'client' => $this->client->getTitle(),
                      'errors' => yii\helpers\Json::encode($auth->getErrors()),
                   ]),
                ]);

             }
          } else { // there's existing auth
             Yii::$app->getSession()->setFlash('error', [
                Yii::t('app',
                   'Unable to link {client} account. There is another user using it.',
                   ['client' => $this->client->getTitle()]),
             ]);
          }
       }
    }

    /**
     * @param User $user
     * @return void
     * @throws Exception
     */
    private function updateUserInfo(User $user)
    {
        $attributes = $this->client->getUserAttributes();
        $github = ArrayHelper::getValue($attributes, 'login');
        if ($github) {
            if ($user->github === null) {
                $user->github = $github;
                $user->save();
            }
        }
    }
}