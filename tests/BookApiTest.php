<?php
namespace App\Tests;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class BookApiTest extends WebTestCase
{
  protected static $example;

  protected function setUp(): void
  {
    parent::setUp();

    self::$example = [
      'title' => Uuid::v4(),
      'description' => Uuid::v4(),
      'publish_date' => date('Y-m-d'),
      'author_id' => 1
    ];
  }

  protected static $lastInsertedId;

  public function testInsertBook(): void
  {
    echo " testInsertBook\n";
    
    $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);
    
    $client->request('POST', '/books/', self::$example);
    
    $response = $client->getResponse();

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertJson($response->getContent());

    $jsonData = json_decode($response->getContent(), true);
    $jsonData = $jsonData['data'];

    // Validate inserted data

    self::$lastInsertedId = $jsonData['id'];
    $client->request('GET', '/books/' . $jsonData['id']);

    $response = $client->getResponse();

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertJson($response->getContent());

    $jsonData = json_decode($response->getContent(), true);
    $jsonData = $jsonData['data'];

    $this->assertEquals($jsonData['title'], self::$example['title']);
    $this->assertEquals($jsonData['description'], self::$example['description']);
    $this->assertEquals($jsonData['publish_date'], self::$example['publish_date']);
    $this->assertEquals($jsonData['author_id'], self::$example['author_id']);
  }

  public function testUpdateBook(): void
  {
    echo " testUpdateBook\n";
    $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);
    $update = [
      'title' => Uuid::v4(),
      'description' => Uuid::v4(),
      'publish_date' => date('Y-m-d'),
      'author_id' => 1
    ];

    $client->request('PUT', '/books/' . self::$lastInsertedId, $update);

    $response = $client->getResponse();
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertJson($response->getContent());

    $jsonData = json_decode($response->getContent(), true);
    $jsonData = $jsonData['data'];

    // Validate inserted data

    $client->request('GET', '/books/' . $jsonData['id']);

    $response = $client->getResponse();

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertJson($response->getContent());

    $jsonData = json_decode($response->getContent(), true);
    $jsonData = $jsonData['data'];

    $this->assertEquals($jsonData['title'], $update['title']);
    $this->assertEquals($jsonData['description'], $update['description']);
    $this->assertEquals($jsonData['publish_date'], $update['publish_date']);
    $this->assertEquals($jsonData['author_id'], $update['author_id']);
  }

  public function testGetBookDetails(): void
  {
    echo " testGetBookDetails\n";
    $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);

    $client->request('GET', '/books/1');
    $response = $client->getResponse();

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertJson($response->getContent());

    $jsonData = json_decode($response->getContent(), true);
    $jsonData = $jsonData['data'];

    $this->assertArrayHasKey('id', $jsonData);
    $this->assertArrayHasKey('title', $jsonData);
    $this->assertArrayHasKey('description', $jsonData);
    $this->assertArrayHasKey('publish_date', $jsonData);
    $this->assertArrayHasKey('author_id', $jsonData);

    $this->assertIsInt($jsonData['id']);
    $this->assertIsString($jsonData['title']);
    $this->assertIsString($jsonData['description']);
    $this->assertIsInt($jsonData['author_id']);

    $date = DateTime::createFromFormat('Y-m-d', $jsonData['publish_date']);
    $this->assertInstanceOf(DateTime::class, $date);
  }

  public function testBookNotFound(): void
  {
    echo " testBookNotFound\n";
    $client = static::createClient(server: ['HTTP_HOST' => 'localhost:8001']);

    $client->request('GET', '/books/999');
    $response = $client->getResponse();

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    $this->assertJson($response->getContent());

    $jsonData = json_decode($response->getContent(), true);
    $this->assertArrayHasKey('message', $jsonData);
    $this->assertEquals('Not Found', $jsonData['message']);
  }
}
