<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventRegistrations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class EventRegistrationController extends AbstractController
{
    #[Route('/admin/api/events/{event}/registrations', methods: ['GET'], name: 'app.get_event_registration_list')]
    public function getEventRegistrations(EntityManagerInterface $em, Event $event): JsonResponse
    {
        $eventRegistration = $em->getRepository(EventRegistrations::class)->find($event);
        if ($eventRegistration === null) {
            throw new \Exception('event registration not found.');
        }
        $registrations = array_map(static fn (EventRegistrations $registrations) => [
            'id' => $registrations->getId(),
            'email' => $registrations->getEmail(),
            'firstname' => $registrations->getFirstname(),
            'lastname' => $registrations->getLastname(),
        ], $event->getEventRegistrations()->toArray());
        $eventRegistrations = ['id' => $event->getId(), 'registrations' => $registrations];
        return new JsonResponse($eventRegistrations, Response::HTTP_OK);
    }
}
