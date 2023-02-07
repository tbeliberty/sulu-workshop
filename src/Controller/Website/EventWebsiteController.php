<?php

declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\Event;
use App\Entity\EventRegistrations;
use App\Form\EventRegistrationsType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EventWebsiteController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly TemplateAttributeResolverInterface $templateAttributeResolver,
    ) {
    }

    #[Route('/{_locale}/event/{id}', name: 'app.event')]
    public function indexAction(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $success = null;
        $event = $this->eventRepository->findById($id, $request->getLocale());
        if (!$event instanceof Event) {
            throw new NotFoundHttpException();
        }
        $registration = new EventRegistrations();
        $registration->setEvents($event);
        $form = $this->createForm(EventRegistrationsType::class, $registration);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($registration);
            $em->flush();
            $success = true;
            return $this->redirectToRoute('app.event', ['id' => $event->getId(),'success' => true,]);
        }
        return $this->render(
            'events/index.html.twig',
            $this->templateAttributeResolver->resolve(
                [
                    'event' => $event,
                    'content' => ['title' => $event->getTitle()],
                    'form' => $form->createView(),
                    'success' => $request->query->get('success')
                ],
            ),
        );
    }
}
