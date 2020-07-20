<?php


namespace App\Tests\Functional;

use App\Test\CustomApitestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends CustomApitestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = self::createClient();

        $client->request('POST', '/api/users',[
           'json'=>[
               'email'=>'cheeseplease@exemple.com',
               'username'=>'cheeseplease',
               'password'=>'brie'
           ]
        ]);
        $this->assertResponseStatusCodeSame(201);

        $this->login($client, 'cheeseplease@exemple.com', 'brie');
    }
}