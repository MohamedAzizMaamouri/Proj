<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/orders')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_admin_orders', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $status = $request->query->get('status');

        $orders = $orderRepository->findPaginatedOrders($page, $limit, $status);
        $totalOrders = $orderRepository->countOrders($status);
        $totalPages = ceil($totalOrders / $limit);

        $statusCounts = [
            'pending' => $orderRepository->countByStatus('pending'),
            'processing' => $orderRepository->countByStatus('processing'),
            'shipped' => $orderRepository->countByStatus('shipped'),
            'delivered' => $orderRepository->countByStatus('delivered'),
            'cancelled' => $orderRepository->countByStatus('cancelled'),
        ];

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_orders' => $totalOrders,
            'current_status' => $status,
            'status_counts' => $statusCounts,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $status = $request->request->get('status');

            if ($status) {
                $order->setStatus($status);
                $entityManager->flush();
                $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
            }

            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
        }

        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
        ]);
    }
}
