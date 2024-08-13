document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll("#card-container .col-md-3");
  const loadMoreButton = document.getElementById("load-more");

  let visibleCount = 0;
  const initialVisible = 8; // Nombre de cartes visibles au début
  const loadMoreCount = 4; // Nombre de cartes à afficher à chaque clic

  // Fonction pour afficher un nombre spécifique de cartes
  function showCards(count) {
    for (let i = visibleCount; i < count; i++) {
      if (cards[i]) {
        cards[i].classList.remove("d-none");
      }
    }
    visibleCount = count;
  }

  // Afficher les premières `initialVisible` cartes
  showCards(initialVisible);

  // Gérer le clic sur le bouton "Load More"
  loadMoreButton.addEventListener("click", () => {
    const newVisibleCount = Math.min(
      visibleCount + loadMoreCount,
      cards.length
    );
    showCards(newVisibleCount);

    if (newVisibleCount >= cards.length) {
      loadMoreButton.setAttribute("disabled", true);
      loadMoreButton.classList.add("btn-secondary");
      loadMoreButton.textContent = "No more tricks to load";
    }
  });
});
