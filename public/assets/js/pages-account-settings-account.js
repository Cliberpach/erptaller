/**
 * Account Settings - Account
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const accountUserImage = document.getElementById('uploadedAvatar');
  const fileInput = document.querySelector('.account-file-input');
  const resetFileInput = document.querySelector('.account-image-reset');

  if (accountUserImage) {
    const resetImage = accountUserImage.src;

    fileInput.onchange = () => {
        const selectedFile = fileInput.files[0];
        accountUserImage.src = URL.createObjectURL(selectedFile);
    };

    resetFileInput.onclick = () => {
        fileInput.value = '';
        accountUserImage.src = resetImage;
    };
  }
});
