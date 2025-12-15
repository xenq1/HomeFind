<?php

namespace App\DataFixtures;

use App\Entity\ActivityLog;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Get users from repository
        $users = $manager->getRepository(User::class)->findAll();
        
        $admin = null;
        $staff = null;

        // Find admin and staff users
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $admin = $user;
            }
            if (in_array('ROLE_STAFF', $user->getRoles())) {
                $staff = $user;
            }
        }

        // Create sample activity logs
        if ($admin) {
            $activities = [
                ['action' => 'Property Created', 'targetData' => 'Modern House in Makati', '192.168.1.1'],
                ['action' => 'User Created', 'targetData' => 'user@example.com', '192.168.1.2'],
                ['action' => 'Property Updated', 'targetData' => 'Condo Unit 502', '192.168.1.1'],
                ['action' => 'Property Deleted', 'targetData' => 'Old Apartment', '192.168.1.3'],
                ['action' => 'User Login', 'targetData' => 'admin@homefind.com', '192.168.1.1'],
                ['action' => 'User Role Changed', 'targetData' => 'john_doe to ROLE_ADMIN', '192.168.1.2'],
            ];
            
            foreach ($activities as $activityData) {
                $log = new ActivityLog();
                $log->setUserId($admin);
                $log->setAction($activityData[0]);
                $log->setTargetData($activityData[1]);
                $log->setIpAddress($activityData[2]);
                $log->setCreatedAt(new \DateTime());
                $manager->persist($log);
            }
        }

        // Create sample properties for staff
        if ($staff) {
            $sampleProperties = [
                [
                    'name' => 'Modern Condo in BGC',
                    'price' => '5500000',
                    'location' => 'Bonifacio Global City, Taguig',
                    'area' => 85,
                    'type' => 'Condo',
                    'status' => 'available',
                    'listingType' => 'for_sale',
                ],
                [
                    'name' => 'House in Quezon City',
                    'price' => '8500000',
                    'location' => 'Quezon City',
                    'area' => 150,
                    'type' => 'House',
                    'status' => 'available',
                    'listingType' => 'for_sale',
                ],
                [
                    'name' => 'Apartment for Rent',
                    'price' => '25000',
                    'location' => 'Makati City',
                    'area' => 50,
                    'type' => 'Apartment',
                    'status' => 'available',
                    'listingType' => 'for_rent',
                ],
            ];

            foreach ($sampleProperties as $propData) {
                $property = new Property();
                $property->setName($propData['name']);
                $property->setPrice($propData['price']);
                $property->setLocation($propData['location']);
                $property->setArea($propData['area']);
                $property->setType($propData['type']);
                $property->setStatus($propData['status']);
                $property->setListingType($propData['listingType']);
                $property->setCreatedBy($staff);
                $property->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($property);
            }
        }

        $manager->flush();
    }
}


