<?php

declare(strict_types=1);

namespace App\Tests\Functional\Pages;

use App\Tests\Functional\Traits\EventTrait;
use App\Tests\Functional\Traits\PageTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\DocumentManager\DocumentManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventOverviewTest extends SuluTestCase
{
    use EventTrait;
    use PageTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = $this->createWebsiteClient();
        $this->initPhpcr();
    }

    public function testEventOverview(): void
    {
        $event1 = $this->createEvent('Sulu is awesome', 'en');
        $this->enableEvent($event1);
        $event2 = $this->createEvent('Symfony Live is awesome', 'en');
        $this->enableEvent($event2);
        $event3 = $this->createEvent('Disabled', 'en');

        $this->createPage(
            'event_overview',
            'example',
            [
                'title' => 'Symfony Live',
                'url' => '/events',
                'published' => true,
            ],
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/en/events');

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Symfony Live', $crawler->filter('h1')->html());
        $this->assertNotNull($content = $crawler->filter('.event-title')->eq(0)->html());
        $this->assertStringContainsString($event1->getTitle() ?: '', $content);
        $this->assertNotNull($content = $crawler->filter('.event-title')->eq(1)->html());
        $this->assertStringContainsString($event2->getTitle() ?: '', $content);
    }

    protected static function getDocumentManager(): DocumentManagerInterface
    {
        return static::getContainer()->get('sulu_document_manager.document_manager');
    }
}
