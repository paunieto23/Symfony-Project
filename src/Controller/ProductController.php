<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestor de productes del SymfoPop.
 * Permet visualitzar el catàleg, detalls i gestionar l'inventari personal.
 */
#[Route('/product')]
final class ProductController extends AbstractController
{
    /**
     * Catàleg públic de productes.
     * Mostra tots els ítems disponibles al mercat ordenats per data.
     */
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'show_new_button' => true,
            'title' => 'Catàleg de Productes',
        ]);
    }

    /**
     * Endpoint de prova per crear un producte ràpidament (Només per desenvolupament).
     */
    #[Route('/test-create', name: 'app_product_test_create', methods: ['GET'])]
    public function testCreate(EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(\App\Entity\User::class)->findOneBy([]);
        if (!$user) {
            return new Response('Cap usuari trobat.');
        }

        $p = new Product();
        $p->setTitle('Test Web ' . uniqid());
        $p->setDescription('Descripció de prova des de la web.');
        $p->setPrice('99.99');
        $p->setOwner($user);
        
        $entityManager->persist($p);
        $entityManager->flush();

        return new Response('Producte creat: ' . $p->getId());
    }

    /**
     * Publicar un nou producte.
     * Requereix estar autenticat (ROLE_USER).
     */
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Assignem l'usuari actual com a propietari
                $product->setOwner($this->getUser());
                
                // Si no hi ha imatge, en generem una per defecte de Picsum per a que la web es vegi bé
                if (!$product->getImage()) {
                    $product->setImage('https://picsum.photos/seed/' . uniqid() . '/800/600');
                }

                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', 'Producte publicat correctament! Ara tothom el pot veure.');
                
                // Redirigim al detall del producte acabat de crear com demana la guia
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('error', 'Hi ha errors al formulari. Si us plau, revisa els camps.');
            }
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * Vista de "Els meus productes".
     * Reutilitza el llistat principal amb opcions de gestió (editar/esborrar).
     */
    #[Route('/my-products', name: 'app_my_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myProducts(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['owner' => $this->getUser()], ['createdAt' => 'DESC']);

        // Fem servir el mateix template index.html.twig amb paràmetres per aplicar DRY
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'title' => 'Els meus Productes',
            'empty_message' => 'Encara no has publicat cap producte. Anima\'t!',
            'show_actions' => true,
            'show_new_button' => true,
        ]);
    }

    /**
     * Fitxa de detall d'un producte.
     */
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Editar un producte existent.
     * Només permès si l'usuari és el propietari (owner).
     */
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // Seguretat: Verificació de propietat
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No ets el propietari d\'aquest producte.');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Producte actualitzat correctament!');
                return $this->redirectToRoute('app_product_show', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('error', 'No s\'han pogut guardar els canvis. Revisa el formulari.');
            }
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * Eliminar un producte.
     * Requereix validació de propietat i token CSRF.
     */
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No ets el propietari d\'aquest producte.');
        }

        // Validació del token CSRF per seguretat contra atacs de segrest de sessió
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Producte eliminat. Ja no està disponible al mercat.');
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
