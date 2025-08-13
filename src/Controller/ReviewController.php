<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\ReviewHelpful;
use App\Entity\ReviewReport;
use App\Repository\ReviewHelpfulRepository;
use App\Repository\ReviewReportRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/api/reviews')]
class ReviewController extends AbstractController
{
    public function __construct(
        private ReviewRepository $reviews,
        private ReviewHelpfulRepository $helpfuls,
        private ReviewReportRepository $reports,
    ) {}

    #[Route('', name: 'app_reviews_list', methods: ['GET'])]
    public function list(Request $r): JsonResponse
    {
        $type   = $r->query->get('mediaType', Review::MEDIA_MOVIE);
        $tmdbId = (int) $r->query->get('tmdbId', 0);
        $sort   = $r->query->get('sort', 'recent');
        $page   = max(1, (int)$r->query->get('page', 1));
        $limit  = min(50, max(1, (int)$r->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        if (!$tmdbId) {
            return $this->json(['error' => 'tmdbId required'], 400);
        }

        $items = $this->reviews->listByMedia($type, $tmdbId, $sort, $limit, $offset);
        $total = $this->reviews->countByMedia($type, $tmdbId);

        $data = array_map(function (Review $r) {
            return [
                'id' => $r->getId(),
                'user' => $r->getUser()?->getEmail(), // ou username si dispo
                'title' => $r->getTitle(),
                'body' => $r->getBody(),
                'rating' => $r->getRating(),
                'createdAt' => $r->getCreatedAt()->format(DATE_ATOM),
                'helpfulCount' => $r->getHelpfulCount(),
                'reportedCount' => $r->getReportedCount(),
            ];
        }, $items);

        return $this->json([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'items' => $data,
        ]);
    }

    #[Route('', name: 'app_reviews_upsert', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upsert(Request $req, CsrfTokenManagerInterface $csrf): JsonResponse
    {
        $payload = json_decode($req->getContent() ?: '[]', true);
        $token = $payload['_token'] ?? null;
        if (!$token || !$this->isCsrfTokenValid('review', $token)) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }

        $type   = $payload['mediaType'] ?? Review::MEDIA_MOVIE;
        $tmdbId = (int)($payload['tmdbId'] ?? 0);
        $title  = trim((string)($payload['title'] ?? ''));
        $body   = trim((string)($payload['body'] ?? ''));
        $rating = isset($payload['rating']) ? (int)$payload['rating'] : null;

        if (!$tmdbId || $title === '' || $body === '') {
            return $this->json(['error' => 'Missing fields'], 422);
        }
        if ($rating !== null && ($rating < 1 || $rating > 10)) {
            return $this->json(['error' => 'Rating must be 1–10'], 422);
        }

        $review = $this->reviews->upsertForUserMedia(
            $this->getUser(),
            $type,
            $tmdbId,
            ['title' => $title, 'body' => $body, 'rating' => $rating]
        );

        return $this->json(['ok' => true, 'id' => $review->getId()]);
    }

    #[Route('/{id}/helpful', name: 'app_reviews_helpful', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function helpful(int $id, Request $req, CsrfTokenManagerInterface $csrf): JsonResponse
    {
        $payload = json_decode($req->getContent() ?: '[]', true);
        $token = $payload['_token'] ?? null;
        if (!$token || !$this->isCsrfTokenValid('review_helpful', $token)) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }

        /** @var Review|null $review */
        $review = $this->reviews->find($id);
        if (!$review || $review->isDeleted()) {
            return $this->json(['error' => 'Not found'], 404);
        }

        // toggle (utile = 1) — si déjà présent, on ne double pas
        $existing = $this->helpfuls->findOneBy(['review' => $review, 'user' => $this->getUser()]);
        $em = $this->getDoctrine()->getManager();

        if ($existing) {
            // idempotent: toggle off
            $em->remove($existing);
            $review->incHelpfulCount(-1);
        } else {
            $h = (new ReviewHelpful())->setReview($review)->setUser($this->getUser())->setValue(true);
            $em->persist($h);
            $review->incHelpfulCount(+1);
        }
        $em->flush();

        return $this->json(['ok' => true, 'helpfulCount' => $review->getHelpfulCount()]);
    }

    #[Route('/{id}/report', name: 'app_reviews_report', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function report(int $id, Request $req): JsonResponse
    {
        $payload = json_decode($req->getContent() ?: '[]', true);
        $token = $payload['_token'] ?? null;
        if (!$token || !$this->isCsrfTokenValid('review_report', $token)) {
            return $this->json(['error' => 'Invalid CSRF token'], 400);
        }

        $reason = trim((string)($payload['reason'] ?? ''));
        if ($reason === '') {
            return $this->json(['error' => 'Reason required'], 422);
        }

        /** @var Review|null $review */
        $review = $this->reviews->find($id);
        if (!$review) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $rep = (new ReviewReport())
            ->setReview($review)
            ->setUser($this->getUser())
            ->setReason(mb_substr($reason, 0, 140));

        $em = $this->getDoctrine()->getManager();
        $em->persist($rep);
        $review->incReportedCount(+1);
        $em->flush();

        return $this->json(['ok' => true]);
    }

    #[Route('/{id}', name: 'app_reviews_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_MODERATOR')]
    public function delete(int $id): JsonResponse
    {
        /** @var Review|null $review */
        $review = $this->reviews->find($id);
        if (!$review) {
            return $this->json(['error' => 'Not found'], 404);
        }
        $review->setIsDeleted(true);
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['ok' => true]);
    }

    private function getDoctrine()
    {
        // Compat helper si AbstractController::getDoctrine() deprecated dans ta version
        return $this->container->get('doctrine');
    }
}

