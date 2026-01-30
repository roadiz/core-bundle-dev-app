# Formulaires personnalisés

## Édition et consultation des formulaires existants

![Capture d’écran 2022-03-04 à 16.52.43.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.52.43.webp)

Les boutons d’actions de droite vous permettent de :

- éditer
- voir les questions
- voir les réponses
- afficher le formulaire (en prévisualisation)
- dupliquer
- exporter
- supprimer

## Créer un nouveau formulaire

![Capture d’écran 2024-03-15 à 15.36.45.webp](formulaires_personnalises/Capture_decran_2024-03-15_a_15.36.45.webp)

<video controls>
    <source src="/user/formulaires_personnalises/Enregistrement_de_lecran_2024-03-15_a_15.38.46.webm" type="video/webm">
    Your browser does not support the video tag.
</video>

Pour créer un formulaire, il est impératif de lui donner un nom (champ obligatoire). Le **nom du formulaire s’affichera en front**.
Ne renseignez pas seulement “Formulaire” (précisez le contexte, par exemple “*Contactez le service Mécénat*”).

Cinq onglets vous permettent de gérer vos formulaires.

![Capture d’écran 2024-03-15 à 15.39.41.webp](formulaires_personnalises/Capture_decran_2024-03-15_a_15.39.41.webp)

## Paramètres du formulaire

### Email de destination

Le mail de destination est également obligatoire. Ce sera le mail qui recevra les résultats du formulaire.

::: tip
💡 Vous avez la possibilité de renseigner plusieurs emails de destination, en les séparant par des virgules.
:::

![Capture d’écran 2024-03-15 à 15.40.27.webp](formulaires_personnalises/Capture_decran_2024-03-15_a_15.40.27.webp)

### Autres

Le champ Description et la couleur sont facultatifs.
Vous avez la possibilité de saisir une date de fin du formulaire (date de fermeture des inscriptions, par exemple).

![Capture d’écran 2024-03-15 à 15.41.15.webp](formulaires_personnalises/Capture_decran_2024-03-15_a_15.41.15.webp)

### Durée de conservation - RGPD

Pour vous conformer au RGPD, renseignez une durée de conservation des réponses maximale.

![Capture d’écran 2024-03-15 à 15.41.45.webp](formulaires_personnalises/Capture_decran_2024-03-15_a_15.41.45.webp)

### Bouton “Ouvert”

Ce bouton permet de publier ou dépublier le formulaire (active et désactive l’affichage du formulaire en front).

### Message de succès

Côté utilisateur, une fois que le formulaire est rempli et envoyé, nous pouvons afficher un message de succès, type “Votre message a été envoyé”.
Ce texte **n’est pas administrable** (codé en dur). Il s’agit d’un message commun à tous les formulaires (non personnalisable).

::: tip
À noter qu'il n'y a pas de mail de confirmation envoyé automatiquement à la soumission d'un formulaire ;
le message de succès s'affichera en front, directement sur la page du site concernée.
:::

## Questions du formulaire

![Capture d’écran 2022-03-04 à 16.00.48.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.00.48.webp)

Permet de saisir tous les champs du formulaire. Pour ajouter une question :

![Capture d’écran 2022-03-04 à 16.01.27.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.01.27.webp)

Renseignez les paramètres de la question :

![Capture d’écran 2022-03-04 à 16.20.14.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.20.14.webp)

### Label

Champ obligatoire ; il s’agit de l’intitulé que verra l’utilisateur en front.

### Description

*Facultatif* : permet d’ajouter une mention à votre question.

Exemple dans le back-office :

![Capture d’écran 2022-03-04 à 16.36.32.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.36.32.webp)

Prévisualisation :

![Capture d’écran 2022-03-04 à 16.36.43.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.36.43.webp)

### Placeholder

*Facultatif* : il s’agit d’un item qui sera affiché dans un champ ne contenant aucune donnée.
Par exemple, pour une liste déroulante, vous pouvez afficher “Sélectionnez”.

### Type

Il s’agit de la typologie de la réponse ; attention, cela contraint l’utilisateur à respecter le format renseigné.
*En cas de doute, laissez par défaut “Chaîne de caractères”, ce qui permettra à l’utilisateur de saisir un texte libre.*

::: tip
💡 Vous avez la possibilité d’afficher une liste déroulante de pays ; il s’agit de la norme ISO 3166-1.
:::

### Requis

Active le champ obligatoire.

### Étendu

Utilise des boîtes à cocher ou des boutons radio à la place de la liste déroulante. Pertinent en cas de listes courtes.
Pour les listes longues, n’activez pas le bouton étendu.

Exemple de liste déroulante :

![Capture d’écran 2022-03-04 à 16.47.26.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.47.26.webp)

Exemple de boutons radio :

![Capture d’écran 2022-03-04 à 16.47.44.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.47.44.webp)

### Valeur(s) par défaut

Permet de mettre en place une liste de choix (pour choix unique ou choix multiple des listes déroulantes et
boutons radio/cases à cocher). Entrez les valeurs séparées par des virgules. Exemple :

![Capture d’écran 2022-03-04 à 16.29.25.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.29.25.webp)

### Nom du groupe

Si votre formulaire est composé d’une seule partie, laissez le champ vide. Si le formulaire comprend plusieurs parties, 
rentrez le nom du groupe pour chaque question.

Exemple dans le back-office :

![Capture d’écran 2022-03-04 à 16.34.47.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.34.47.webp)

Prévisualisation :

![Capture d’écran 2022-03-04 à 16.34.00.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.34.00.webp)

### Ordre des questions

Les questions se placent par ordre de leur création.

Pour changer l’ordre des questions, utilisez le glisser-déposer :

<video controls>
    <source src="/user/formulaires_personnalises/Enregistrement_de_lecran_2022-03-04_a_16.56.05.webm" type="video/webm">
    Your browser does not support the video tag.
</video>

## Réponses du formulaire

Cet onglet vous permet d’accéder à toutes les réponses reçues et de les exporter.

![Capture d’écran 2022-03-04 à 16.49.55.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.49.55.webp)

## Prévisualiser le formulaire 👁️‍🗨️

Permet de prévisualiser votre formulaire.

![Capture d’écran 2022-03-04 à 16.55.00.webp](formulaires_personnalises/Capture_decran_2022-03-04_a_16.55.00.webp)

# Intégration d’un Formulaire personnalisé

Vous avez la possibilité d’intégrer un formulaire personnalisé dans un bloc prévu à cet effet.
