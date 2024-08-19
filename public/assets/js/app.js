function showToast(message) {
  const toastElement = document.getElementById("notificationToast");
  const toastBody = toastElement.querySelector(".toast-body");
  toastBody.textContent = message;
  const toast = new bootstrap.Toast(toastElement);
  toast.show();
}
