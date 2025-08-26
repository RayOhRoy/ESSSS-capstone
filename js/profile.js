const uploadInput = document.getElementById("uploadProfile");
const profileImage = document.getElementById("profileImage");

uploadInput.addEventListener("change", function() {
  console.log("File input changed");  // <-- Add this for debugging
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      profileImage.src = e.target.result;
      console.log("Image src updated");  // <-- Also for debugging
    }
    reader.readAsDataURL(file);
  }
});