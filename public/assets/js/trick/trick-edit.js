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
            refreshMedias();
            showToast("Picture deleted successfully!");
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
            refreshMedias();
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
  const slugElement = document.getElementById("slug");
  const trickSlug = slugElement.getAttribute("data-slug");
  const nameElement = document.getElementById("name");
  const trickName = nameElement.getAttribute("data-name");

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
            // Afficher la notification toast
            showToast("Picture added successfully !");
            // Fermer le modal
            bootstrap.Modal.getInstance(
              document.getElementById("addPicture")
            ).hide();
            refreshMedias();
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
            refreshMedias();
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

      const inputElement = editPictureForm.querySelector(
        'input[name="picture"]'
      );
      if (inputElement) {
        inputElement.value = ""; // Vide l'input à chaque ouverture du modal
      }

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

      fetch(actionUrl, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            refreshMedias();
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
            refreshMedias();

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

  // Fonction pour mettre à jour la bannière et les médias après une modification
  let mediasContainer = document.querySelector(".medias-container");
  let bannerContainer = document.querySelector(".banner");

  // Fonction pour mettre à jour la bannière
  function updateBanner(trick) {
    bannerContainer.innerHTML = ""; // Vider la bannière

    let imgSrc =
      trick.pictures.length > 0
        ? `/assets/uploads/${trick.pictures[0].url}`
        : "/assets/uploads/default-picture.png";

    const bannerHTML = `
      <img class="card-img-top img-fluid rounded w-100 border border-light" 
          src="${imgSrc}" 
          data-id="${trick.pictures.length > 0 ? trick.pictures[0].id : ""}">
      <div class="position-absolute top-0 end-0 p-3 bg-primary rounded m-2 bg-primary-dark d-flex gap-2 shadow">
          <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#addPicture">
              <i class="bi bi-lg bi-pencil mx-2 text-light"></i>
          </div>
      </div>
      <div class="overlay-text display-1 position-absolute text-light">${trickName}</div>
      ${
        trick.pictures.length > 0
          ? `<div class="position-absolute top-0 end-0 p-3 bg-primary rounded m-2 bg-primary-dark d-flex gap-2 shadow">
              <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#editPictureModal"
                  data-id="${trick.pictures[0].id}" 
                  data-url="/trick/${trickSlug}/edit-picture/${trick.pictures[0].id}" 
                  data-token="${trick.pictures[0].csrfToken}">
                  <i class="bi bi-lg bi-pencil mx-2 text-light"></i>
              </div>
              <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#deletePictureModal"
                  data-url="/trick/${trickSlug}/delete-picture/${trick.pictures[0].id}" 
                  data-trick-slug="${trickSlug}" 
                  data-picture-id="${trick.pictures[0].id}" 
                  data-token="${trick.pictures[0].csrfToken}">
                  <i class="bi bi-lg bi-trash text-danger"></i>
              </div>
          </div>`
          : ""
      }
    `;

    bannerContainer.insertAdjacentHTML("beforeend", bannerHTML);
  }

  function updateMedias(trick) {
    mediasContainer.innerHTML = ""; // Vider les médias

    if (trick.pictures.length > 1) {
      trick.pictures.slice(1).forEach((picture) => {
        const pictureHTML = `
          <div class="picture col-md-2 p-2 d-flex flex-column gap-2">
              <img src="/assets/uploads/${picture.url}" class="img-fluid rounded cursor-pointer border border-light" data-bs-toggle="modal" data-bs-target="#pictureModal" data-id="${picture.id}" data-url="/assets/uploads/${picture.url}">
              <div class="edit-container col-3 col-lg-12 d-flex gap-2 rounded justify-content-center align-self-center bg-primary-dark">
                  <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#editPictureModal" data-id="${picture.id}" data-url="/trick/${trickSlug}/edit-picture/${picture.id}" data-token="${picture.csrfToken}">
                      <i class="bi bi-lg bi-pencil mx-2 text-light"></i>
                  </div>
                  <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#deletePictureModal" data-url="/trick/${trickSlug}/delete-picture/${picture.id}" data-token="${picture.csrfToken}">
                      <i class="bi bi-lg bi-trash text-danger"></i>
                  </div>
              </div>
          </div>
        `;
        mediasContainer.insertAdjacentHTML("beforeend", pictureHTML);
      });
    }
    trick.videos.forEach((video) => {
      const videoHTML = `
        <div class="video col-md-2 p-2 d-flex flex-column gap-2">
            <div class="video-container">
                <iframe class="rounded border border-light" width="100%" height="100%" data-id="${video.id}" src="https://www.youtube.com/embed/${video.url}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
            <div class="edit-container col-3 col-lg-12 d-flex gap-2 rounded justify-content-center align-self-center bg-primary-dark">
                <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#editVideoModal" data-id="${video.id}" data-url="/trick/${trickSlug}/edit-video/${video.id}" data-token="${video.csrfToken}">
                    <i class="bi bi-lg bi-pencil mx-2 text-light"></i>
                </div>
                <div class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#deleteVideoModal" data-url="/trick/${trickSlug}/delete-video/${video.id}" data-token="${video.csrfToken}">
                    <i class="bi bi-lg bi-trash text-danger"></i>
                </div>
            </div>
        </div>
      `;
      mediasContainer.insertAdjacentHTML("beforeend", videoHTML);
    });

    const addMediaButtonsHTML = `
      <div class="col-md-2 p-2 d-flex flex-column gap-2">
          <button 
              class="add btn btn-primary d-flex flex-column justify-content-center align-items-center text-white border border-white rounded h-100 cursor-pointer"
              data-bs-toggle="modal" 
              data-bs-target="#addPicture"
          >
              <div>+ picture</div>
          </button>
          <div class="edit-container w-50 d-flex gap-2 rounded align-self-end invisible">
                  <a><i class="bi bi-lg bi-pencil mx-2 text-light"></i></a>
                  <a><i class="bi bi-lg bi-trash text-light"></i></a>
          </div>
      </div>
      <div class="col-md-2 p-2 d-flex flex-column gap-2">
          <button 
              class="btn btn-primary add d-flex flex-column justify-content-center align-items-center text-white border border-white rounded h-100 cursor-pointer"
              data-bs-toggle="modal" 
              data-bs-target="#addVideo"
          >
              <div>+ video</div>
          </button>
          <div class="edit-container w-50 d-flex gap-2 rounded align-self-end invisible">
                  <a><i class="bi bi-lg bi-pencil mx-2 text-light"></i></a>
                  <a><i class="bi bi-lg bi-trash text-light"></i></a>
          </div>
      </div>
    `;
    mediasContainer.insertAdjacentHTML("beforeend", addMediaButtonsHTML);
  }

  function refreshMedias() {
    fetch(`/trick/${trickSlug}/get-media`)
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data) {
          updateBanner(data);
          updateMedias(data);
        } else {
          console.error("Failed to fetch trick media data");
        }
      })
      .catch((error) => console.error("Error fetching media data:", error));
  }
});
