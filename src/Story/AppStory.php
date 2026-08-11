<?php

namespace App\Story;

use App\Factory\AddressFactory;
use App\Factory\MissionFactory;
use App\Factory\PackageFactory;
use App\Factory\PlaceFactory;
use App\Factory\UserFactory;
use App\Factory\VehicleFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{

    public function build(): void
    {
//        PlaceFactory::createMany(10, [
//            'address' => AddressFactory::new(),
//        ]);

        VehicleFactory::createMany(15);

        MissionFactory::createMany( 6, [
            'pickupPlace' => PlaceFactory::new([
                'address' => AddressFactory::new(),
            ]),
            'deliveryPlace' => PlaceFactory::new([
                'address' => AddressFactory::new(),
            ]),
            'packages' => PackageFactory::new()->range(1,2)
        ]);

        for ($i = 1; $i <= 3; $i++) {
            UserFactory::new([
                'username' => 'guido' . $i,
                'roles' => ["ROLE_DRIVER"]
            ])->create();
        }

        UserFactory::new([
            'username' => 'andrea',
            'roles' => ["ROLE_ADMIN","ROLE_DRIVER"]
        ])->create();

    }
}
