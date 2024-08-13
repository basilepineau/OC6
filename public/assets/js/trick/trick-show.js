document.addEventListener("DOMContentLoaded", () => {
  const commentContainer = document.getElementById("comment-container");
  const loadMoreButton = document.getElementById("load-more");
  let visibleCount = 0;
  const startNumber = 5;

  function disableLoadMoreButton() {
    loadMoreButton.setAttribute("disabled", true);
    loadMoreButton.classList.add("text-muted");
    loadMoreButton.innerHTML = "All comments are loaded.";
  }

  // Fonction pour afficher un nombre spécifique de commentaires
  function showComments(count) {
    const comments = commentContainer.querySelectorAll(".comment");
    for (let i = visibleCount; i < count; i++) {
      if (comments[i]) {
        comments[i].classList.remove("d-none");
      }
    }
    visibleCount = count;
    if (comments.length <= startNumber) {
      disableLoadMoreButton();
    }
  }

  // Afficher les premiers `startNumber` commentaires
  showComments(startNumber);

  // Gérer le clic sur le bouton "Charger Plus"
  loadMoreButton.addEventListener("click", () => {
    const comments = commentContainer.querySelectorAll(".comment");
    const newVisibleCount = Math.min(
      visibleCount + startNumber,
      comments.length
    );
    showComments(newVisibleCount);

    if (newVisibleCount >= comments.length) {
      disableLoadMoreButton();
    }
  });
});
