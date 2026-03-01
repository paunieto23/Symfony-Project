<?php
require 'vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

try {
    $user = $em->getRepository(App\Entity\User::class)->findOneBy([]);
    if (!$user) {
        echo "NO USER FOUND\n";
        exit;
    }

    $p = new App\Entity\Product();
    $p->setTitle('Manual Test Product');
    $p->setDescription('This is a manual test product description');
    $p->setPrice('100.00');
    $p->setOwner($user);
    $p->setCreatedAt(new \DateTimeImmutable());

    $em->persist($p);
    $em->flush();
    echo "PRODUCT CREATED SUCCESSFULLY\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
