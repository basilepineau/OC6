document.addEventListener("DOMContentLoaded", () => {
  let btnSeeMedias = document.getElementById("see-medias");
  let picturesContainer = document.querySelector(".pictures-container");
  btnSeeMedias.addEventListener("click", function () {
    if (picturesContainer.classList.contains("d-none")) {
      picturesContainer.classList.remove("d-none");
      btnSeeMedias.textContent = "Hide medias";
    } else {
      picturesContainer.classList.add("d-none");
      btnSeeMedias.textContent = "See medias";
    }
  });

  // Fonction pour agrandir les images
  const pictureModal = document.getElementById("pictureModal");
  const modalImage = document.getElementById("modalImage");

  pictureModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const imageUrl = button.getAttribute("data-url");
    modalImage.src = imageUrl;
  });

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

  // Fonction pour supprimer les commentaires
  const deleteCommentModal = document.getElementById("deleteCommentModal");
  const deleteCommentForm = document.getElementById("deleteCommentForm");

  if (deleteCommentModal && deleteCommentForm) {
    deleteCommentModal.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");

      if (url && token) {
        // Update the form action and token
        deleteCommentForm.action = url;
        deleteCommentForm.querySelector('input[name="_token"]').value = token;

        // Store a reference to the button that triggered the modal
        deleteCommentModal.setAttribute("data-trigger-url", url);
      } else {
        console.error("URL or CSRF token is missing.");
      }
    });

    deleteCommentForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(deleteCommentForm);
      const actionUrl = deleteCommentForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Hide the modal
            const bsModal = bootstrap.Modal.getInstance(deleteCommentModal);
            bsModal.hide();

            // Find the button that triggered the modal using the stored URL
            const triggerUrl =
              deleteCommentModal.getAttribute("data-trigger-url");
            const button = document.querySelector(`[data-url="${triggerUrl}"]`);

            // Find the closest .comment and remove it
            if (button) {
              const CommentElement = button.closest(".comment");
              if (CommentElement) {
                CommentElement.remove();
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
  const editCommentModal = document.getElementById("editCommentModal");
  const editCommentForm = document.getElementById("editCommentForm");

  if (editCommentModal && editCommentForm) {
    editCommentModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");
      const id = button.getAttribute("data-id");
      const text = button.getAttribute("data-text");

      if (url && token && id && text !== null) {
        // Mettre à jour l'action du formulaire et le token CSRF
        editCommentForm.action = url;
        editCommentForm.querySelector('input[name="_token"]').value = token;
        editCommentModal.setAttribute("data-comment-id", id);

        // Pré-remplir le textarea avec le texte du commentaire
        document.getElementById("edit-comment-text").value = text;
      } else {
        console.error("URL, CSRF token, ID, or comment text is missing.");
      }
    });

    editCommentForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(editCommentForm);
      const actionUrl = editCommentForm.action;

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
            bootstrap.Modal.getInstance(editCommentModal).hide();
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
