document.addEventListener("DOMContentLoaded", () => {
  // Fonction pour agrandir les images
  const pictureModal = document.getElementById("pictureModal");
  const modalImage = document.getElementById("modalImage");

  pictureModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const imageUrl = button.getAttribute("data-url");
    modalImage.src = imageUrl;
  });

  // Fonction pour supprimer les images
  const deletePictureModal = document.getElementById("deletePictureModal");
  const deletePictureForm = document.getElementById("deletePictureForm");

  if (deletePictureModal && deletePictureForm) {
    deletePictureModal.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");

      if (url && token) {
        // Update the form action and token
        deletePictureForm.action = url;
        deletePictureForm.querySelector('input[name="_token"]').value = token;

        // Store a reference to the button that triggered the modal
        deletePictureModal.setAttribute("data-trigger-url", url);
      } else {
        console.error("URL or CSRF token is missing.");
      }
    });

    deletePictureForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(deletePictureForm);
      const actionUrl = deletePictureForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Hide the modal
            const bsModal = bootstrap.Modal.getInstance(deletePictureModal);
            bsModal.hide();

            // Find the button that triggered the modal using the stored URL
            const triggerUrl =
              deletePictureModal.getAttribute("data-trigger-url");
            const button = document.querySelector(`[data-url="${triggerUrl}"]`);

            // Find the closest .col-md-2 and remove it
            if (button) {
              const pictureElement = button.closest(".col-md-2");
              if (pictureElement) {
                pictureElement.remove();
              } else {
                console.error(
                  "Unable to find the parent element with class '.col-md-2'"
                );
              }
            } else {
              console.error(
                "Unable to find the button with data-url:",
                triggerUrl
              );
            }
            showToast("Picture deleted successfully !");
          } else {
            alert(
              data.error || "An error occurred while deleting the picture."
            );
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }

  // Fonction pour supprimer les videos
  const deleteVideoModal = document.getElementById("deleteVideoModal");
  const deleteVideoForm = document.getElementById("deleteVideoForm");

  if (deleteVideoModal && deleteVideoForm) {
    deleteVideoModal.addEventListener("show.bs.modal", function (event) {
      // Button that triggered the modal
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");

      if (url && token) {
        // Update the form action and token
        deleteVideoForm.action = url;
        deleteVideoForm.querySelector('input[name="_token"]').value = token;

        // Store a reference to the button that triggered the modal
        deleteVideoModal.setAttribute("data-trigger-url", url);
      } else {
        console.error("URL or CSRF token is missing.");
      }
    });

    deleteVideoForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(deleteVideoForm);
      const actionUrl = deleteVideoForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Hide the modal
            const bsModal = bootstrap.Modal.getInstance(deleteVideoModal);
            bsModal.hide();

            // Find the button that triggered the modal using the stored URL
            const triggerUrl =
              deleteVideoModal.getAttribute("data-trigger-url");
            const button = document.querySelector(`[data-url="${triggerUrl}"]`);

            // Find the closest .col-md-2 and remove it
            if (button) {
              const videoElement = button.closest(".col-md-2");
              if (videoElement) {
                videoElement.remove();
              } else {
                console.error(
                  "Unable to find the parent element with class '.col-md-2'"
                );
              }
            } else {
              console.error(
                "Unable to find the button with data-url:",
                triggerUrl
              );
            }
            showToast("Video deleted successfully !");
          } else {
            alert(data.error || "An error occurred while deleting the video.");
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }

  // Fonction pour ajouter une image
  const addPictureForm = document.getElementById("addPictureForm");
  const trickSlug = "{{ trick.slug }}";

  // Function to insert after the last picture or video
  function insertAfterLastElementOfClass(newElement, className) {
    // Find all elements with the specified class
    const elements = document.getElementsByClassName(className);
    if (elements.length > 0) {
      // Get the last element with the specified class
      const lastElement = elements[elements.length - 1];
      // Insert the new element after the last element
      lastElement.parentNode.insertBefore(newElement, lastElement.nextSibling);
    } else {
      // If no elements with the class exist, append the new element at the end of the container
      const container = document.querySelector(".row.p-4"); // Update with the correct container selector
      container.appendChild(newElement);
    }
  }

  if (addPictureForm) {
    addPictureForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(addPictureForm);
      const actionUrl = addPictureForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Create a new picture element dynamically
            const newPictureDiv = document.createElement("div");
            newPictureDiv.classList.add(
              "col-md-2",
              "p-2",
              "d-flex",
              "flex-column",
              "gap-2",
              "picture"
            );
            newPictureDiv.innerHTML = `
                        <img src="/assets/uploads/${data.url}" class="img-fluid rounded cursor-pointer" data-bs-toggle="modal" data-bs-target="#pictureModal" data-url="/assets/uploads/${data.url}">
                        <div class="edit-container w-50 d-flex gap-2 rounded align-self-end">
                            <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#editPictureModal"><i class="bi bi-lg bi-pencil mx-2 text-light"></i></div>
                            <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#deletePictureModal" data-url="${data.deleteUrl}" data-token="${data.csrfToken}"><i class="bi bi-lg bi-trash text-light"></i></div>
                        </div>
                    `;

            insertAfterLastElementOfClass(newPictureDiv, "picture");
            // Afficher la notification toast
            showToast("Picture added successfully !");
            // Fermer le modal
            bootstrap.Modal.getInstance(
              document.getElementById("addPicture")
            ).hide();
          } else {
            alert(data.error || "An error occurred while adding the picture.");
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }

  // Fonction pour ajouter une vidéo
  const addVideoForm = document.getElementById("addVideoForm");
  if (addVideoForm) {
    addVideoForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(addVideoForm);
      const actionUrl = addVideoForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Ajoute dynamiquement la vidéo à la page
            const newVideoDiv = document.createElement("div");
            newVideoDiv.classList.add(
              "col-md-2",
              "p-2",
              "d-flex",
              "flex-column",
              "gap-2",
              "video"
            );
            newVideoDiv.innerHTML = `
                <div class="video-container">
                    <iframe class="rounded" width="100%" height="100%" src="https://www.youtube.com/embed/${data.url}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <div class="edit-container w-50 d-flex gap-2 rounded align-self-end">
                    <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#editVideoModal"><i class="bi bi-lg bi-pencil mx-2 text-light"></i></div>
                    <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#deleteVideoModal" data-url="${data.deleteUrl}" data-token="${data.csrfToken}"><i class="bi bi-lg bi-trash text-light"></i></div>
                </div>
            `;
            insertAfterLastElementOfClass(newVideoDiv, "video");

            // Afficher la notification toast
            showToast("Video added successfully !");
            // Fermer le modal
            bootstrap.Modal.getInstance(
              document.getElementById("addVideo")
            ).hide();
          } else {
            alert(data.error || "An error occurred while adding the video.");
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }

  // Fonction pour éditer les images
  const editPictureModal = document.getElementById("editPictureModal");
  const editPictureForm = document.getElementById("editPictureForm");

  if (editPictureModal && editPictureForm) {
    editPictureModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");
      const id = button.getAttribute("data-id");

      if (url && token && id) {
        editPictureForm.action = url;
        editPictureForm.querySelector('input[name="_token"]').value = token;
        editPictureModal.setAttribute("data-picture-id", id);
      } else {
        console.error("URL, CSRF token, or ID is missing.");
      }
    });

    editPictureForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(editPictureForm);
      const actionUrl = editPictureForm.action;
      console.log(actionUrl);

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const id = editPictureModal.getAttribute("data-picture-id");
            const imgElement = document.querySelector(`img[data-id="${id}"]`);
            if (imgElement) {
              imgElement.src = "/assets/uploads/" + data.url;
            }

            const deleteButton = document.querySelector(
              `div[data-id="${id}"] .cursor-pointer[data-bs-target="#deletePictureModal"]`
            );
            if (deleteButton) {
              deleteButton.setAttribute("data-token", data.csrfToken);
            }

            showToast("Picture updated successfully!");
            bootstrap.Modal.getInstance(editPictureModal).hide();
          } else {
            alert(data.error || "An error occurred while editing the picture.");
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }

  // Fonction pour éditer les vidéos
  const editVideoModal = document.getElementById("editVideoModal");
  const editVideoForm = document.getElementById("editVideoForm");

  if (editVideoModal && editVideoForm) {
    editVideoModal.addEventListener("show.bs.modal", function (event) {
      const button = event.relatedTarget;
      const url = button.getAttribute("data-url");
      const token = button.getAttribute("data-token");
      const id = button.getAttribute("data-id");

      if (url && token && id) {
        editVideoForm.action = url;
        editVideoForm.querySelector('input[name="_token"]').value = token;
        editVideoModal.setAttribute("data-video-id", id);
      } else {
        console.error("URL, CSRF token, or ID is missing.");
      }
    });

    editVideoForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(editVideoForm);
      const actionUrl = editVideoForm.action;

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const id = editVideoModal.getAttribute("data-video-id");
            const iframeElement = document.querySelector(
              `iframe[data-id="${id}"]`
            );
            if (iframeElement) {
              iframeElement.src = "https://www.youtube.com/embed/" + data.url;
            }

            showToast("Video updated successfully!");
            bootstrap.Modal.getInstance(editVideoModal).hide();
          } else {
            alert(data.error || "An error occurred while editing the video.");
          }
        })
        .catch((error) => {
          alert("An error occurred: " + error.message);
        });
    });
  }
});
