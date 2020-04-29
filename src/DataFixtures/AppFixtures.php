<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Clients;

class AppFixtures extends Fixture
{
    private const CLIENTS = [
        [
        'document'=>'0123',
        'name'=>'pedro perez',
        'email'=>'p@p.com',
        'phone'=>'123123'
        ],
        [
            'document'=>'0123324',
            'name'=>'pedro carlos',
            'email'=>'p@p2.com',
            'phone'=>'123123'
        ],
        [
            'document'=>'0123',
            'name'=>'pedro ramon',
            'email'=>'p@p3.com',
            'phone'=>'123123'
        ]
    ];
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $this->loadCliens($manager);
    }

    private function loadCliens($manager){
        
        foreach (self::CLIENTS as $key => $value) {
            $client = new Clients();
            $client->setDocument($value['document']);
            $client->setName($value['name']);
            $client->setEmail($value['email']);
            $client->setPhone($value['phone']);
            $manager->persist($client);
        }
        $manager->flush();
    }
}
