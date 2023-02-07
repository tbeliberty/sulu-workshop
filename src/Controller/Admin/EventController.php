<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Common\DoctrineListRepresentationFactory;
use App\Entity\Event;
use App\Repository\EventRepository;
use DateTimeImmutable;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @phpstan-type EventData array{
 *     id: int|null,
 *     enabled: bool,
 *     title: string,
 *     image: array{id: int}|null,
 *     teaser: string|null,
 *     description: string|null,
 *     startDate: string|null,
 *     endDate: string|null,
 *     location: string|null,
 * }
 */
class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
    ) {
    }

    #[Route(path: '/admin/api/events/{id}', name: 'app.get_event', methods: ['GET'])]
    public function getAction(int $id, Request $request): Response
    {
        $event = $this->load($id, $request);
        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        return $this->json($this->getDataForEntity($event));
    }

    #[Route(path: '/admin/api/events/{id}', name: 'app.put_event', methods: ['PUT'])]
    public function putAction(int $id, Request $request): Response
    {
        $event = $this->load($id, $request);
        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        /** @var EventData $data */
        $data = $request->toArray();
        $this->mapDataToEntity((array)$data, $event);
        $this->save($event);

        return $this->json($this->getDataForEntity($event));
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/admin/api/events', name: 'app.post_event', methods: ['POST'])]
    public function postAction(Request $request): Response
    {
        $event = $this->create($request);

        /** @var EventData $data */
        $data = $request->toArray();
        $this->mapDataToEntity((array)$data, $event);
        $this->save($event);

        return $this->json($this->getDataForEntity($event), 201);
    }

    #[Route(path: '/admin/api/events/{id}', name: 'app.post_event_trigger', methods: ['POST'])]
    public function postTriggerAction(int $id, Request $request): Response
    {
        $event = $this->eventRepository->findById($id, (string) $this->getLocale($request));
        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }

        switch ($request->query->get('action')) {
            case 'enable':
                $event->setEnabled(true);
                break;
            case 'disable':
                $event->setEnabled(false);
                break;
        }

        $this->eventRepository->save($event);

        return $this->json($this->getDataForEntity($event));
    }

    #[Route(path: '/admin/api/events/{id}', name: 'app.delete_event', methods: ['DELETE'])]
    public function deleteAction(int $id): Response
    {
        $this->remove($id);

        return $this->json(null, 204);
    }

    #[Route(path: '/admin/api/events', name: 'app.get_event_list', methods: ['GET'])]
    public function getListAction(Request $request): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Event::RESOURCE_KEY,
            [],
            ['locale' => $this->getLocale($request)],
        );

        return $this->json($listRepresentation->toArray());
    }

    /**
     * @param Event $entity
     * @return array $data
     */
    protected function getDataForEntity(Event $entity): array
    {
        $image = $entity->getImage();
        $startDate = $entity->getStartDate();
        $endDate = $entity->getEndDate();

        return [
            'id' => $entity->getId(),
            'enabled' => $entity->isEnabled(),
            'title' => $entity->getTitle() ?? '',
            'image' => null !== $image
                ? ['id' => $image->getId()]
                : null,
            'teaser' => $entity->getTeaser(),
            'description' => $entity->getDescription(),
            'startDate' => $startDate?->format('c'),
            'endDate' => $endDate?->format('c'),
            'location' => $entity->getLocation(),
        ];
    }

    /**
     * @param array $data
     * @param Event $entity
     * @throws \Exception
     */
    protected function mapDataToEntity(array $data, Event $entity): void
    {
        $imageId = $data['image']['id'] ?? null;

        $entity->setTitle($data['title']);
        $entity->setImage($imageId ? $this->mediaRepository->findMediaById($imageId) : null);
        $entity->setTeaser($data['teaser'] ?? '');
        $entity->setDescription($data['description'] ?? '');
        $entity->setStartDate($data['startDate'] ? new DateTimeImmutable($data['startDate']) : null);
        $entity->setEndDate($data['endDate'] ? new DateTimeImmutable($data['endDate']) : null);
        $entity->setLocation($data['location'] ?? null);
    }

    protected function load(int $id, Request $request): ?Event
    {
        return $this->eventRepository->findById($id, (string) $this->getLocale($request));
    }

    protected function create(Request $request): Event
    {
        return $this->eventRepository->create((string) $this->getLocale($request));
    }

    protected function save(Event $entity): void
    {
        $this->eventRepository->save($entity);
    }

    protected function remove(int $id): void
    {
        $this->eventRepository->remove($id);
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->has('locale') ? (string) $request->query->get('locale') : null;
    }
}
