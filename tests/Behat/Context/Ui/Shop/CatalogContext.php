<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\RawMinkContext;
use Webmozart\Assert\Assert;

/**
 * Context pour les fonctionnalités de catalogue (browsing_bouquets.feature)
 */
final class CatalogContext extends RawMinkContext implements Context
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @Given que les produits suivants existent dans le catalogue:
     */
    public function lesProduitsExistent(array $products): void
    {
        // Cette méthode délègue normalement à un SetupContext
        // qui va créer les produits en base via les fixtures
        // Ici on simule juste pour l'exemple
    }

    /**
     * @When je visite la page :path
     * @When je suis sur la page :path
     */
    public function jeVisiteLaPage(string $path): void
    {
        $this->session->visit($path);
    }

    /**
     * @Then je devrais voir :text
     */
    public function jeDevraisVoir(string $text): void
    {
        $page = $this->session->getPage();
        Assert::true(
            $page->hasContent($text),
            sprintf('Le texte "%s" devrait être présent sur la page', $text)
        );
    }

    /**
     * @Then je ne devrais pas voir :text
     */
    public function jeNeDevraisPasVoir(string $text): void
    {
        $page = $this->session->getPage();
        Assert::false(
            $page->hasContent($text),
            sprintf('Le texte "%s" ne devrait pas être présent sur la page', $text)
        );
    }

    /**
     * @When je filtre par occasion :occasion
     */
    public function jeFiltreParOccasion(string $occasion): void
    {
        $page = $this->session->getPage();

        // Chercher le filtre par occasion
        $filter = $page->find('css', '#occasion-filter');
        Assert::notNull($filter, 'Le filtre occasion devrait être présent');

        // Sélectionner l'occasion
        $filter->selectOption($occasion);

        // Attendre que la page se recharge (peut être AJAX)
        $this->session->wait(1000);
    }

    /**
     * @When je filtre par catégorie :category
     */
    public function jeFiltreParCategorie(string $category): void
    {
        $page = $this->session->getPage();

        $categoryLink = $page->findLink($category);
        Assert::notNull($categoryLink, sprintf('La catégorie "%s" devrait être présente', $category));

        $categoryLink->click();
    }

    /**
     * @When je trie par :sortOption
     */
    public function jeTriePar(string $sortOption): void
    {
        $page = $this->session->getPage();

        $sortSelect = $page->find('css', '#sort-by');
        Assert::notNull($sortSelect, 'Le sélecteur de tri devrait être présent');

        $sortSelect->selectOption($sortOption);
        $this->session->wait(1000);
    }

    /**
     * @Then le premier bouquet affiché devrait être :productName
     */
    public function lePremierBouquetDevrait(string $productName): void
    {
        $page = $this->session->getPage();

        $firstProduct = $page->find('css', '.product-item:first-child .product-name');
        Assert::notNull($firstProduct, 'Le premier produit devrait être présent');

        $actualName = $firstProduct->getText();
        Assert::same(
            $actualName,
            $productName,
            sprintf('Le premier produit devrait être "%s" mais est "%s"', $productName, $actualName)
        );
    }

    /**
     * @Then le dernier bouquet affiché devrait être :productName
     */
    public function leDernierBouquetDevrait(string $productName): void
    {
        $page = $this->session->getPage();

        $lastProduct = $page->find('css', '.product-item:last-child .product-name');
        Assert::notNull($lastProduct, 'Le dernier produit devrait être présent');

        $actualName = $lastProduct->getText();
        Assert::same(
            $actualName,
            $productName,
            sprintf('Le dernier produit devrait être "%s" mais est "%s"', $productName, $actualName)
        );
    }

    /**
     * @When je clique sur :linkText
     */
    public function jeCliqueSur(string $linkText): void
    {
        $page = $this->session->getPage();

        $link = $page->findLink($linkText);
        if (null === $link) {
            // Essayer avec un bouton
            $link = $page->findButton($linkText);
        }

        Assert::notNull($link, sprintf('Le lien/bouton "%s" devrait être présent', $linkText));
        $link->click();
    }

    /**
     * @Then je devrais être sur la page de détail du produit
     */
    public function jeDevraisEtreSurPageDetailProduit(): void
    {
        $currentUrl = $this->session->getCurrentUrl();
        Assert::contains($currentUrl, '/products/', 'L\'URL devrait contenir "/products/"');
    }

    /**
     * @Then je devrais voir le bouton :buttonText
     */
    public function jeDevraisVoirLeBouton(string $buttonText): void
    {
        $page = $this->session->getPage();

        $button = $page->findButton($buttonText);
        Assert::notNull($button, sprintf('Le bouton "%s" devrait être présent', $buttonText));
        Assert::true($button->isVisible(), sprintf('Le bouton "%s" devrait être visible', $buttonText));
    }

    /**
     * @Then je devrais voir la section :sectionName
     */
    public function jeDevraisVoirLaSection(string $sectionName): void
    {
        $page = $this->session->getPage();

        // Chercher une section avec cet heading
        $section = $page->find('xpath', sprintf('//h2[contains(text(), "%s")] | //h3[contains(text(), "%s")]', $sectionName, $sectionName));
        Assert::notNull($section, sprintf('La section "%s" devrait être présente', $sectionName));
    }

    /**
     * @When je recherche :searchTerm
     */
    public function jeRecherche(string $searchTerm): void
    {
        $page = $this->session->getPage();

        $searchInput = $page->find('css', '#search-input, input[name="search"]');
        Assert::notNull($searchInput, 'Le champ de recherche devrait être présent');

        $searchInput->setValue($searchTerm);

        // Submit le formulaire
        $searchForm = $searchInput->getParent();
        while ($searchForm && 'form' !== $searchForm->getTagName()) {
            $searchForm = $searchForm->getParent();
        }

        if ($searchForm) {
            $searchForm->submit();
        } else {
            // Sinon appuyer sur Enter
            $searchInput->keyPress(13);
        }
    }

    /**
     * @Then je devrais voir :count résultats
     * @Then je devrais voir :count résultat
     */
    public function jeDevraisVoirResultats(string $count): void
    {
        $page = $this->session->getPage();

        // Chercher le nombre de résultats
        $resultCount = $page->find('css', '.result-count');
        if ($resultCount) {
            $text = $resultCount->getText();
            Assert::contains($text, $count, sprintf('Le nombre de résultats devrait contenir "%s"', $count));
        } else {
            // Compter les produits affichés
            $products = $page->findAll('css', '.product-item');
            Assert::count($products, (int) $count, sprintf('Il devrait y avoir %s résultat(s)', $count));
        }
    }

    /**
     * @When je clique sur l'icône :iconName
     */
    public function jeCliqueSurIcone(string $iconName): void
    {
        $page = $this->session->getPage();

        // Chercher une icône avec aria-label ou title
        $icon = $page->find('css', sprintf('[aria-label*="%s"], [title*="%s"]', $iconName, $iconName));
        Assert::notNull($icon, sprintf('L\'icône "%s" devrait être présente', $iconName));

        $icon->click();
    }

    /**
     * @Then les bouquets devraient s'afficher en mode grille
     */
    public function lesBouquetsDevraientSAfficherEnModeGrille(): void
    {
        $page = $this->session->getPage();

        $productsContainer = $page->find('css', '.products-grid, .products.grid-view');
        Assert::notNull($productsContainer, 'Le conteneur de produits en grille devrait être présent');
    }

    /**
     * @Then chaque bouquet devrait afficher une grande image
     */
    public function chaqueBouquetDevrait(): void
    {
        $page = $this->session->getPage();

        $products = $page->findAll('css', '.product-item');
        Assert::greaterThan(count($products), 0, 'Il devrait y avoir au moins un produit');

        foreach ($products as $product) {
            $image = $product->find('css', 'img.product-image');
            Assert::notNull($image, 'Chaque produit devrait avoir une image');
        }
    }

    /**
     * @Then je devrais voir les prix en surimpression
     */
    public function jeDevraisVoirLesPrixEnSurimpression(): void
    {
        $page = $this->session->getPage();

        $priceOverlays = $page->findAll('css', '.product-item .price-overlay');
        Assert::greaterThan(count($priceOverlays), 0, 'Il devrait y avoir des prix en surimpression');
    }
}
