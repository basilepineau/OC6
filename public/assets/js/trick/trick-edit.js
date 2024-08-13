document.addEventListener("DOMContentLoaded", () => {
  const deleteConfirmationModal = document.getElementById(
    "deleteConfirmationModal"
  );

  // On injecte les données du trick dans le form au moment de l'ouverture du modam
  // Comme ça, le code pourra être réutilisé lorsqu'il y aura plusieurs tricks sur une même page
  // On injecte en même temps un jeton CSRF pour la sécurité
  deleteConfirmationModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const url = button.getAttribute("data-url");
    const token = button.getAttribute("data-token");

    let form = document.getElementById("deleteForm");
    form.action = url;

    form.querySelector('input[name="_token"]').value = token;
  });
});
