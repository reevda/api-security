<?php

namespace App\Test\Functional;

use App\Entity\CheeseListing;
use App\Test\CustomApitestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends CustomApitestCase
{
    use ReloadDatabaseTrait;

    public function testCreateCheeseListing()
    {
        $client = self::createClient();

        /**  Accès sans authentitifcation à l'endpoint */
        $client->request('POST', '/api/cheeses', [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(401);

        /** Création et login de l'utilisateur */
        $user = $this->createUserAndLogin($client, 'cheeseplease@example.com', 'foo');

        /** Authentification OK, pas de données passées => 400  */
        $client->request('POST', '/api/cheeses', [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(400);

        /** Authentification OK, saisie d'une entrée => 201  */
        $client->request('POST', '/api/cheeses', [
            'json' => [
                'title'=>'My new cheese',
                'description'=>'My new cheese description',
                'price'=>1000,
                'isPublished'=>true,
                'owner'=>'/api/users/'.$user->getId()
            ]
        ]);
        $this->assertResponseStatusCodeSame(201);
    }

    public function testUpdateCheeseListing()
    {
        $client = self::createClient();
        $user1 = $this->createUser('user1@example.com', 'foo');
        $user2 = $this->createUser('user2@example.com', 'foo');
        $cheeseListing = new CheeseListing('Block of cheddar');
        $cheeseListing->setOwner($user1);
        $cheeseListing->setPrice(1000);
        $cheeseListing->setDescription('mmmm');

        $em = $this->getEntityManager();
        $em->persist($cheeseListing);
        $em->flush();

        $this->login($client, 'user2@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(),[
            'json' => ['title' => 'updated', 'description'=>'nnnnh']
        ]);
        $this->assertResponseStatusCodeSame(403);
//        var_dump($client->getResponse()->getContent(false));

        $this->login($client, 'user1@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(),[
            'json' => ['title' => 'updated', 'description'=>'nnnnh']
        ]);
        $this->assertResponseStatusCodeSame(200);
    }
}