document.addEventListener("DOMContentLoaded", () => {
  const commentContainer = document.getElementById("comment-container");
  const loadMoreButton = document.getElementById("load-more-comments");
  let visibleCount = 0;
  const startNumber = 5;

  function disableLoadMoreButton() {
    if (loadMoreButton) {
      loadMoreButton.setAttribute("disabled", true);
      loadMoreButton.classList.add("text-muted");
      loadMoreButton.innerHTML = "All comments are loaded.";
    }
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
  if (loadMoreButton) {
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
  }

  // Fonction pour supprimer les commentaires
  const deleteCommentMainModal = document.getElementById(
    "deleteCommentMainModal"
  );
  const deleteCommentMainForm = document.getElementById(
    "deleteCommentMainForm"
  );

  if (deleteCommentMainModal && deleteCommentMainForm) {
    deleteCommentMainModal.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");

      if (url && token) {
        // Update the form action and token
        deleteCommentMainForm.action = url;
        deleteCommentMainForm.querySelector('input[name="_token"]').value =
          token;

        // Store a reference to the button that triggered the modal
        deleteCommentMainModal.setAttribute("data-trigger-url", url);
      } else {
        console.error("URL or CSRF token is missing.");
      }
    });

    deleteCommentMainForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(deleteCommentMainForm);
      const actionUrl = deleteCommentMainForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Hide the modal
            const bsModal = bootstrap.Modal.getInstance(deleteCommentMainModal);
            bsModal.hide();

            // Find the button that triggered the modal using the stored URL
            const triggerUrl =
              deleteCommentMainModal.getAttribute("data-trigger-url");
            const button = document.querySelector(`[data-url="${triggerUrl}"]`);

            // Find the closest .comment and remove it
            if (button) {
              const commentMainElement = button.closest(".comment");
              if (commentMainElement) {
                commentMainElement.remove();
              } else {
                console.error(
                  "Unable to find the parent element with class '.comment'"
                );
              }
            } else {
              console.error(
                "Unable to find the button with data-url:",
                triggerUrl
              );
            }
            showToast("Comment deleted successfully !");
          } else {
            alert(
              data.error || "An error occurred while deleting the comment."
            );
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }

  // Fonction pour éditer les commentaires
  const editCommentMainModal = document.getElementById("editCommentMainModal");
  const editCommentMainForm = document.getElementById("editCommentMainForm");

  if (editCommentMainModal && editCommentMainForm) {
    editCommentMainModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");
      const id = button.getAttribute("data-id");
      const text = button.getAttribute("data-text");

      if (url && token && id && text !== null) {
        // Mettre à jour l'action du formulaire et le token CSRF
        editCommentMainForm.action = url;
        editCommentMainForm.querySelector('input[name="_token"]').value = token;
        editCommentMainModal.setAttribute("data-comment-main-id", id);

        // Pré-remplir le textarea avec le texte du commentaire
        document.getElementById("edit-comment-main-text").value = text;
      } else {
        console.error("URL, CSRF token, ID, or comment text is missing.");
      }
    });

    editCommentMainForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(editCommentMainForm);
      const actionUrl = editCommentMainForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Mettre à jour le commentaire affiché avec le texte modifié
            document.getElementById(data.id).innerText = data.text;

            // Mettre à jour le data-text de l'élément déclencheur
            const triggerButton = document.querySelector(
              `[data-id="${data.id}"]`
            );
            if (triggerButton) {
              triggerButton.setAttribute("data-text", data.text);
            }

            showToast("Comment updated successfully!");
            bootstrap.Modal.getInstance(editCommentMainModal).hide();
          } else {
            alert(data.error || "An error occurred while editing the comment.");
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }
});
