<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Basic + Yii2 Auth Client</h1>
    <br>
</p>

Ini adalah contoh Yii2 Auth client yang meng-implementasi Yii2 OAuth2 server dari repository ini: <span>TODO UPLOAD</span>
Cara menggunakannya:
1. Clone Project,
2. Running composer update,
3. Rename `config/db-example.php` menjadi `config/db.php`
4. Running migration,
5. Running server dengan mengetikkan perintah `php yii serve`
6. Aplikasi siap digunakan 

Implementasi Oauth2 pada repo ini ada 2, yaitu:

## Grant Type: Authorization Code
`components/MyOAuth2AuthClient.php`
```php
<?php

namespace app\components;
use yii\authclient\OAuth2;

/**
 * Auth Client yang kita buat sendiri
 * */
class MyOAuth2AuthClient extends OAuth2
{

   public ?string $apiUserInfo = null;

   protected function defaultName(): string
   {
      return 'my-oauth2';
   }

   protected function defaultTitle(): string
   {
      return 'My Oauth2';
   }

   protected function initUserAttributes(): array
   {
      return $this->api($this->apiUserInfo, 'GET', [], ['Authorization' => 'Bearer ' . $this->accessToken->params['access_token']]);
   }

}
```

`config/web.php`
```php
'components' => [
  'authClientCollection' => [
     'class' => 'yii\authclient\Collection',
     'clients' => [
        'google' => [
           'class' => 'yii\authclient\clients\Google',
           'clientId' => 'google_client_id',
           'clientSecret' => 'google_client_secret',
        ],
        'my-oauth2' => [
           'class' => '\app\components\MyOAuth2AuthClient',
           'clientId' => 'testclient',
           'clientSecret' => 'testpass',
           'authUrl' => 'http://localhost:8080/authorize',
           'tokenUrl' => 'http://localhost:8080/oauth2/token',
           'returnUrl' => 'http://localhost:8081/authorize?authclient=my-oauth2',
           'apiBaseUrl' =>  'http://localhost:8080/oauth2/v1',
           'apiUserInfo' => 'http://localhost:8080/oauth2/user-info',
           'viewOptions' => [
              'icon' => 'https://cdn-icons-png.flaticon.com/512/2376/2376399.png'
           ]
        ],
     ],
  ],
  ... another component here
]
```

`controllers/SiteController.php`

```php
<?php
# Metode OAuth2 Authorization Code
public function actions()
{
    return [
         # ... another action here
        'authorize' => [
            'class' => 'yii\authclient\AuthAction',
            'successCallback' => [$this, 'onAuthSuccess'],
        ],
    ];
}

public function onAuthSuccess($client)
{
    (new AuthHandler($client))->handle();
}
```

`components/AuthHandler.php`
```php
<?php

namespace app\components;

# some import here

class AuthHandler
{

    private ClientInterface $client;
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

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

       # Handle user-info to database
       # ....
       
    }
    
}
```

## Grant Type: Client Resource Password
`controllers/SiteController.php`
```php
<?php
class SiteController extends \yii\web\Controller{

    # ... a lot of code here
   
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) return $this->goHome();
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post())) {

           /* @var $client MyOAuth2AuthClient */
           $client = Yii::$app->authClientCollection->getClient('sihrd');

           try {
           
              # Metode Resource Owned Password disini
              if($client->authenticateUser($model->username, $model->password)){
                 
                 # Handle data user
                 $model->loginByOauth2ResourceOwnerPassword($client);
                 return $this->goHome();
              }
           } catch (\Exception $e) {
              $model->addError('password', $e->getMessage());
           }
        }

        $model->password = '';
        return $this->render('login', ['model' => $model,]);
    }   
}

```

`models\LoginForm.php`
```php
class LoginForm extends Model{

   # ... a lot of code here
   
  public function loginByOauth2ResourceOwnerPassword(OAuth2 $client)
   {

      $attributes = $client->getUserAttributes();

      $id = ArrayHelper::getValue($attributes, 'user.id');
      $email = ArrayHelper::getValue($attributes, 'user.email');
      $username = ArrayHelper::getValue($attributes, 'user.username');

      /* @var Auth $auth */
      $auth = Auth::find()->where(['source' => $client->getId(), 'source_id' => $id,])->one();

      // Cek kalau user sudah terdaftar dari SIHRD atau another OAuth2 ?
     
   }
}
```