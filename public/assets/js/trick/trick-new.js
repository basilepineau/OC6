document.addEventListener("DOMContentLoaded", function () {
  // Fonction pour ajouter un champ
  function addField(container) {
    const prototype = container.dataset.prototype;
    const index = container.children.length;
    const newField = prototype.replace(/__name__/g, index);
    container.insertAdjacentHTML("beforeend", newField);
    updateRemoveButtonVisibility(container);
  }

  // Ajouter un champ initial si aucun champ n'est présent
  function addInitialField(container) {
    if (container.children.length === 0) {
      addField(container);
    }
  }

  // Initialiser les champs existants
  document
    .querySelectorAll("#picture-container, #video-container")
    .forEach((container) => {
      addInitialField(container);
    });

  // Gestion des boutons d'ajout
  document.querySelectorAll(".btn-add-more").forEach((button) => {
    button.addEventListener("click", function () {
      const container = document.querySelector(`#${this.dataset.container}`);
      addField(container);
    });
  });

  // Fonction pour supprimer le dernier champ
  function removeLastField(container) {
    if (container.children.length > 1) {
      container.lastElementChild.remove();
      updateRemoveButtonVisibility(container);
    } else {
      alert("You must have at least one item.");
    }
  }

  // Fonction pour mettre à jour la visibilité du bouton de suppression
  function updateRemoveButtonVisibility(container) {
    if (container.id === "picture-container") {
      const removeButton = document.getElementById("remove-picture-btn");
      removeButton.style.display =
        container.children.length > 1 ? "block" : "none";
    } else if (container.id === "video-container") {
      const removeButton = document.getElementById("remove-video-btn");
      removeButton.style.display =
        container.children.length > 1 ? "block" : "none";
    }
  }

  // Gestion des boutons de suppression
  document
    .getElementById("remove-picture-btn")
    .addEventListener("click", function () {
      const container = document.getElementById("picture-container");
      removeLastField(container);
    });

  document
    .getElementById("remove-video-btn")
    .addEventListener("click", function () {
      const container = document.getElementById("video-container");
      removeLastField(container);
    });
});
