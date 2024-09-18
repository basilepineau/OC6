document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll("#card-container .trick-container");
  const loadMoreButton = document.getElementById("load-more-tricks");

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

  if (loadMoreButton) {
    // Gérer le clic sur le bouton "Load more"
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
  }

  // Fonction pour supprimer les tricks
  const deleteTrickModal = document.getElementById("deleteTrickModal");
  const deleteTrickForm = document.getElementById("deleteTrickForm");

  if (deleteTrickModal && deleteTrickForm) {
    deleteTrickModal.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");

      if (url && token) {
        // Update the form action and token
        deleteTrickForm.action = url;
        deleteTrickForm.querySelector('input[name="_token"]').value = token;

        // Store a reference to the button that triggered the modal
        deleteTrickModal.setAttribute("data-trigger-url", url);
      } else {
        console.error("URL or CSRF token is missing.");
      }
    });
  }
});
