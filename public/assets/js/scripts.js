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

  // const cardContainer = document.getElementById("card-container");
  // const loadMoreButton = document.getElementById("load-more");

  // const cardLimit = 61;
  // const cardIncrease = 5;
  // const pageCount = Math.ceil(cardLimit / cardIncrease);

  // let currentPage = 1;

  // const createCard = (index) => {
  //   const card = document.createElement("div");
  //   card.className = "card";
  //   card.innerHTML = index;
  //   cardContainer.appendChild(card);
  // };

  // const addCards = (pageIndex) => {
  //   currentPage = pageIndex;

  //   handleButtonStatus();

  //   const startRange = (pageIndex - 1) * cardIncrease;
  //   const endRange =
  //     currentPage == pageCount ? cardLimit : pageIndex * cardIncrease;

  //   for (let i = startRange + 1; i <= endRange; i++) {
  //     createCard(i);
  //   }
  // };

  // const handleButtonStatus = () => {
  //   if (pageCount === currentPage) {
  //     loadMoreButton.classList.add("disabled");
  //     loadMoreButton.setAttribute("disabled", true);
  //   }
  // };

  // window.onload = function () {
  //   addCards(currentPage);
  //   loadMoreButton.addEventListener("click", () => {
  //     addCards(currentPage + 1);
  //   });
  // };
});
