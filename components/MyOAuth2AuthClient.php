<?php

namespace app\components;
use yii\authclient\OAuth2;

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