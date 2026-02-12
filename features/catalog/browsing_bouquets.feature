# language: fr
Fonctionnalité: Navigation dans le catalogue de bouquets
    En tant que client
    Je veux parcourir les bouquets disponibles
    Afin de trouver le bouquet parfait pour mon occasion

    Contexte:
        Étant donné que les produits suivants existent dans le catalogue:
            | nom                          | prix   | catégorie      | occasion         | disponibilité |
            | Bouquet Romantique Rose      | 45.00  | Roses          | Saint-Valentin   | en_stock      |
            | Composition Élégance         | 65.00  | Mixte          | Anniversaire     | en_stock      |
            | Gerbe de Deuil Blanche       | 89.00  | Funéraire      | Deuil            | en_stock      |
            | Bouquet Naissance Garçon     | 39.00  | Naissance      | Naissance        | en_stock      |
            | Orchidée Premium             | 75.00  | Plantes        | Remerciement     | rupture       |
            | Roses Rouges Prestige (24)   | 120.00 | Roses          | Demande Mariage  | en_stock      |

    Scénario: Afficher tous les bouquets disponibles
        Quand je visite la page "/bouquets"
        Alors je devrais voir "5 bouquets disponibles"
        Et je devrais voir "Bouquet Romantique Rose"
        Et je devrais voir "Composition Élégance"
        Mais je ne devrais pas voir "Orchidée Premium"

    Scénario: Filtrer les bouquets par occasion
        Étant donné que je suis sur la page "/bouquets"
        Quand je filtre par occasion "Saint-Valentin"
        Alors je devrais voir "1 bouquet disponible"
        Et je devrais voir "Bouquet Romantique Rose"
        Mais je ne devrais pas voir "Composition Élégance"

    Scénario: Filtrer les bouquets par catégorie
        Étant donné que je suis sur la page "/bouquets"
        Quand je filtre par catégorie "Roses"
        Alors je devrais voir "2 bouquets disponibles"
        Et je devrais voir "Bouquet Romantique Rose"
        Et je devrais voir "Roses Rouges Prestige (24)"

    Scénario: Trier les bouquets par prix
        Étant donné que je suis sur la page "/bouquets"
        Quand je trie par "Prix croissant"
        Alors le premier bouquet affiché devrait être "Bouquet Naissance Garçon"
        Et le dernier bouquet affiché devrait être "Roses Rouges Prestige (24)"

    Scénario: Afficher les détails d'un bouquet
        Étant donné que je suis sur la page "/bouquets"
        Quand je clique sur "Bouquet Romantique Rose"
        Alors je devrais être sur la page de détail du produit
        Et je devrais voir "45,00 €"
        Et je devrais voir le bouton "Ajouter au panier"
        Et je devrais voir "Livraison possible dès demain"
        Et je devrais voir la section "Personnalisation"

    Scénario: Rechercher un bouquet spécifique
        Étant donné que je suis sur la page "/bouquets"
        Quand je recherche "rose"
        Alors je devrais voir "2 résultats"
        Et je devrais voir "Bouquet Romantique Rose"
        Et je devrais voir "Roses Rouges Prestige (24)"

    @javascript
    Scénario: Visualiser les bouquets en mode galerie
        Étant donné que je suis sur la page "/bouquets"
        Quand je clique sur l'icône "Vue galerie"
        Alors les bouquets devraient s'afficher en mode grille
        Et chaque bouquet devrait afficher une grande image
        Et je devrais voir les prix en surimpression
