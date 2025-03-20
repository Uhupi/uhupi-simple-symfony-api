<?php

namespace App\Tests\Controller;

use App\Entity\Tasks;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TasksControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private static int $testId = 1;
    private string  $path = '/api/tasks';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        $content = $this->client->getResponse()->getContent();
        // Response has to be always a JSON Collection
        $this->assertJson($content);

        // Test Parameters
        $this->client->request('GET', $this->path . '?page=2&limit=15&status=completed');
        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);
        // ... we could do more tests here... (ex. page or limit alone)

        $this->assertResponseIsSuccessful();
    }

    public function testCreate(): void
    {
        $this->client->request('POST', $this->path, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($this->dummyData()));

        $content    = $this->client->getResponse()->getContent();
        $data       = (array) json_decode($content);

        $this->assertResponseStatusCodeSame(201);
        $this->assertArrayHasKey('id', $data);
        $this->assertJson($content);

        // Globalize an id for further testing
        self::$testId = $data['id'];
    }
    
    public function testShow(): void
    {
        // We choose the just created item, of cousser we can choose any other
        $this->client->request('GET', $this->path . '/' . self::$testId);
        $this->assertResponseIsSuccessful();
        // Lets make sure it is the test item
        $data = (array) json_decode($this->client->getResponse()->getContent());
        $this->assertStringEndsWith($data['title'], $this->dummyData()['title']);
    }

    public function testUpdate(): void
    {
        $values = ['title' => 'Task aktualisiert'];
        $this->client->request('PUT', $this->path . '/' . self::$testId, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($values));
        $this->assertResponseIsSuccessful();

        // Lets make sure value title has been updated
        $this->client->request('GET', $this->path . '/' . self::$testId);
        $data = (array) json_decode($this->client->getResponse()->getContent());
        $this->assertStringEndsWith($data['title'], $values['title']);
    }

    public function testRemove(): void
    {
        $this->client->request('DELETE', $this->path . '/' . self::$testId);
        $this->assertResponseStatusCodeSame(204);

        // Lets make sure we get the standard error JSON
        $this->client->request('GET', $this->path . '/' . self::$testId);
        $data = (array) json_decode($this->client->getResponse()->getContent());
        $this->assertTrue($this->isError($data));
    }

    private function isError($data): bool
    {
        return $data['error'];
    }

    /**
     * Only some test data (we could separate ir into a external json file)
     */
    private function dummyData(): array
    {
        return [
            'title'         => 'Neue Aufgabe',
            'description'   => 'Beschreibung der Aufgabe',
            'status'        => 'in_progress',
            'due_date'      => '2024-12-31 12:00:00',
        ];
    }
}
