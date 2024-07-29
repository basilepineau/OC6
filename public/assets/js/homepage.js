/*!
 * Start Bootstrap - Modern Business v5.0.7 (https://startbootstrap.com/template-overviews/modern-business)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-modern-business/blob/master/LICENSE)
 */
// This file is intentionally blank
// Use this file to add JavaScript to your project

document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".card");
  const loadMoreButton = document.getElementById("load-more");

  let visibleCount = 0;
  const startNumber = 5;

  // Fonction pour afficher un nombre spécifique de cartes
  function showCards(count) {
    for (let i = visibleCount; i < count; i++) {
      if (cards[i]) {
        cards[i].classList.remove("d-none");
        console.log(cards[i]);
      }
    }
    visibleCount = count;
  }

  // Afficher les premières `startNumber` cartes
  showCards(startNumber);

  // Gérer le clic sur le bouton "Charger Plus"
  loadMoreButton.addEventListener("click", () => {
    const newVisibleCount = Math.min(visibleCount + startNumber, cards.length);
    showCards(newVisibleCount);

    if (newVisibleCount >= cards.length) {
      loadMoreButton.setAttribute("disabled", true);
      loadMoreButton.classList.add("text-muted");
      loadMoreButton.innerHTML = "All tricks are loaded.";
    }
  });
});
