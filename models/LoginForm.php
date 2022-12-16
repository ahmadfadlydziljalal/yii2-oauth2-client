<?php

namespace app\models;

use Exception;
use Yii;
use yii\authclient\OAuth2;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
   public $username;
   public $password;
   public $rememberMe = true;

   private $_user = false;


   /**
    * @return array the validation rules.
    */
   public function rules()
   {
      return [
         // username and password are both required
         [['username', 'password'], 'required'],
         // rememberMe must be a boolean value
         ['rememberMe', 'boolean'],
         // password is validated by validatePassword()
         ['password', 'validatePassword'],
      ];
   }

   /**
    * Validates the password.
    * This method serves as the inline validation for password.
    *
    * @param string $attribute the attribute currently being validated
    * @param array $params the additional name-value pairs given in the rule
    */
   public function validatePassword($attribute, $params)
   {
      if (!$this->hasErrors()) {
         $user = $this->getUser();

         if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Incorrect username or password.');
         }
      }
   }

   /**
    * Finds user by [[username]]
    *
    * @return User|null
    */
   public function getUser()
   {
      if ($this->_user === false) {
         $this->_user = User::findByUsername($this->username);
      }
      return $this->_user;
   }

   /**
    * @param OAuth2 $client
    * @return void
    * @throws Exception
    */
   public function loginByOauth2ResourceOwnerPassword(OAuth2 $client)
   {

      $attributes = $client->getUserAttributes();

      $id = ArrayHelper::getValue($attributes, 'user.id');
      $email = ArrayHelper::getValue($attributes, 'user.email');
      $username = ArrayHelper::getValue($attributes, 'user.username');

      /* @var Auth $auth */
      $auth = Auth::find()->where(['source' => $client->getId(), 'source_id' => $id,])->one();

      // Cek kalau user sudah terdaftar dari SIHRD atau another OAuth2 ?
      if ($auth) {

         // Update data terakhir user tersebut By User info
         $user = $auth->user;
         $user->id = $id;
         $user->email = $email;
         $user->username = $username;
         $user->save(false);

         Yii::$app->session->setFlash('success', 'Login by ' . ucfirst($client->getId()) . ' berhasil dan Selamat Datang...! ');
         Yii::$app->user->login($user, 84000);

      } else { // User belum terdaftar ?

         $user = new User(['username' => $username, 'email' => $email, 'password' => $this->password,]);
         $user->generateAuthKey();
         $user->generatePasswordResetToken();

         if ($user->save()) {
            $auth = new Auth([
               'user_id' => $user->id,
               'source' => $client->getId(),
               'source_id' => (string)$id,
            ]);
            $auth->save();
            Yii::$app->session->setFlash('success', 'Anda sudah bergabung via ' . ucfirst($client->getId()) . ', dan Selamat Datang...! ');
            Yii::$app->user->login($user, 86400);
         }
      }
   }

   /**
    * Logs in a user using the provided username and password.
    * @return bool whether the user is logged in successfully
    */
   public function login()
   {
      if ($this->validate()) {
         return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
      }
      return false;
   }

   /**
    * @param User $user
    * @return void
    * @throws Exception
    */
   private function updateUserInfo(User $user, $client)
   {
      $attributes = $client->getUserAttributes();
      $github = ArrayHelper::getValue($attributes, 'login');
      if ($github) {
         if ($user->github === null) {
            $user->github = $github;
            $user->save();
         }
      }
   }

}