<?php

namespace App\Tests;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class AuthorApiTest extends WebTestCase
{
    protected static $example;

    protected function setUp(): void
    {
        parent::setUp();

        self::$example = [
            'name' => Uuid::v4(),
            'bio' => Uuid::v4(),
            'birth_date' => date('Y-m-d'),
            'author_id' => 1
        ];
    }

    protected static $lastInsertedId;

    public function testInsertAuthor(): void
    {
        echo " testInsertAuthor\n";

        $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);

        $client->request('POST', '/authors/', self::$example);

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        $jsonData = $jsonData['data'];

        // Validate inserted data

        self::$lastInsertedId = $jsonData['id'];
        $client->request('GET', '/authors/' . $jsonData['id']);

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        $jsonData = $jsonData['data'];

        $this->assertEquals($jsonData['name'], self::$example['name']);
        $this->assertEquals($jsonData['bio'], self::$example['bio']);
        $this->assertEquals($jsonData['birth_date'], self::$example['birth_date']);
    }

    public function testUpdateAuthor(): void
    {
        echo " testUpdateAuthor\n";
        $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);
        $update = [
            'name' => Uuid::v4(),
            'bio' => Uuid::v4(),
            'birth_date' => date('Y-m-d'),
        ];

        $client->request('PUT', '/authors/' . self::$lastInsertedId, $update);

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        $jsonData = $jsonData['data'];
        

        // Validate inserted data

        $client->request('GET', '/authors/' . $jsonData['id']);

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        $jsonData = $jsonData['data'];
        

        $this->assertEquals($jsonData['name'], $update['name']);
        $this->assertEquals($jsonData['bio'], $update['bio']);
        $this->assertEquals($jsonData['birth_date'], $update['birth_date']);
    }

    public function testGetAuthorDetails(): void
    {
        echo " testGetAuthorDetails\n";
        $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);

        $client->request('GET', '/authors/1');
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        $jsonData = $jsonData['data'];
        

        $this->assertArrayHasKey('id', $jsonData);
        $this->assertArrayHasKey('name', $jsonData);
        $this->assertArrayHasKey('bio', $jsonData);
        $this->assertArrayHasKey('birth_date', $jsonData);

        $this->assertIsInt($jsonData['id']);
        $this->assertIsString($jsonData['name']);
        $this->assertIsString($jsonData['bio']);

        $date = DateTime::createFromFormat('Y-m-d', $jsonData['birth_date']);
        $this->assertInstanceOf(DateTime::class, $date);
    }

    public function testGetAuthorBooks(): void
    {
        echo " testGetAuthorBooks\n";
        $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);

        // Test fetching an existing author with books
        $client->request('GET', '/authors/1/books');
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        $jsonData = $jsonData['data'];
        

        foreach ($jsonData as $key => $value) {
            $this->assertIsInt($value['id']);
            $this->assertIsString($value['title']);
            $this->assertIsString($value['description']);
            $this->assertIsInt($value['author_id']);

            $date = DateTime::createFromFormat('Y-m-d', $value['publish_date']);
            $this->assertInstanceOf(DateTime::class, $date);
        }

        $this->assertIsArray($jsonData);
    }

    public function testAuthorNotFound(): void
    {
        echo " testAuthorNotFound\n";
        $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);

        $client->request('GET', '/authors/999');
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $jsonData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('message', $jsonData);
        $this->assertEquals('Not Found', $jsonData['message']);
    }
}
