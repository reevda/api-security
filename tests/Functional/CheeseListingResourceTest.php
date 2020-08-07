<?php

namespace App\tests\Functional;

use App\Entity\CheeseListing;
use App\ApiPlatform\Test\CustomApitestCase;
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
        $authenticatedUser = $this->createUserAndLogin($client, 'cheeseplease@example.com', 'foo');
        $otherUser = $this->createUser('otheruser@example.com', 'foo');


        $cheesyData = [
            'title' => 'Mystery cheese... kinda green',
            'description' => 'What mysteries does it hold?',
            'price' => 5000
        ];

        $client->request('POST', '/api/cheeses', [
            'json' => $cheesyData
        ]);
        $this->assertResponseStatusCodeSame(201);


        $client->request('POST', '/api/cheeses', [
            'json' => $cheesyData + ['owner'=>'/api/users/'.$otherUser->getId()]
        ]);
        $this->assertResponseStatusCodeSame(400, 'not passing the correct owner');

        $client->request('POST', '/api/cheeses', [
            'json' => $cheesyData + ['owner'=>'/api/users/'.$authenticatedUser->getId()]
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
        $client->request('PUT', '/api/cheeses/' . $cheeseListing->getId(), [
            'json' => ['title' => 'updated', 'description' => 'nnnnh']
        ]);
        $this->assertResponseStatusCodeSame(403);
//        var_dump($client->getResponse()->getContent(false));

        $this->login($client, 'user1@example.com', 'foo');
        $client->request('PUT', '/api/cheeses/' . $cheeseListing->getId(), [
            'json' => ['title' => 'updated', 'description' => 'nnnnh']
        ]);
        $this->assertResponseStatusCodeSame(200);
    }
}